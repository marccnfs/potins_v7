<?php

namespace App\Controller\Agenda;

use App\Classe\PublicSession;
use App\Entity\Agenda\Event;
use App\Entity\Users\Participant;
use App\Lib\Links;
use App\Service\Agenda\IcsExporter;
use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
// use App\Security\RequireParticipant; // si tu veux protéger certaines routes

final class AgendaController extends AbstractController
{
    use PublicSession;

    #[Route('/agenda', name: 'agenda_index')]
    public function index(Request $req): Response
    {
        $view = $req->query->get('view', 'month'); // month|week|day
        $date = $req->query->get('date', (new \DateTimeImmutable('today'))->format('Y-m-d'));

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            '_index',
            0,
            "nocity");


        return $this->render('pwa/agenda/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'directory'=>'agenda',
            'view' => $view,
            'date' => new \DateTimeImmutable($date),
        ]);
    }

    #[Route('/agenda/feed.json', name: 'agenda_feed', methods: ['GET'])]
    public function feed(Request $req, EM $em): JsonResponse
    {
        $from = $req->query->get('from'); // YYYY-MM-DD (local Europe/Paris)
        $to   = $req->query->get('to');

        if (!$from || !$to) {
            return new JsonResponse(['error' => 'from/to required'], 400);
        }

        $tzParis = new \DateTimeZone('Europe/Paris');
        $fromLocal = (new \DateTimeImmutable($from.' 00:00:00', $tzParis));
        $toLocal   = (new \DateTimeImmutable($to.' 23:59:59', $tzParis));

        // Converti en UTC pour requête
        $fromUtc = $fromLocal->setTimezone(new \DateTimeZone('UTC'));
        $toUtc   = $toLocal->setTimezone(new \DateTimeZone('UTC'));

        $qb = $em->getRepository(Event::class)->createQueryBuilder('e')
            ->andWhere('e.published = true')
            ->andWhere('e.status = :st')->setParameter('st', 'scheduled')
            ->andWhere('e.startsAt <= :to')->setParameter('to', $toUtc)
            ->andWhere('e.endsAt >= :from')->setParameter('from', $fromUtc)
            ->orderBy('e.startsAt', 'ASC');

        // (Option) filtrer par category ?sourceType ?visibility
        if ($cat = $req->query->get('category')) {
            $qb->andWhere('e.category = :cat')->setParameter('cat', $cat);
        }

        if ($commune = $req->query->get('commune')) {
            $qb->andWhere('e.communeCode = :cc')->setParameter('cc', $commune);
        }


        $events = $qb->getQuery()->getResult();

        // Sérialisation légère (format local FR)
        $out = [];
        foreach ($events as $e) {
            /** @var Event $e */
            $tz = new \DateTimeZone($e->getTimezone());
            $sLocal = $e->getStartsAt()->setTimezone($tz);
            $eLocal = $e->getEndsAt()->setTimezone($tz);

            $out[] = [
                'slug'          => $e->getSlug(),
                'title'         => $e->getTitle(),
                'category'      => $e->getCategory(),
                'startsAtLocal' => $sLocal->format('d/m H:i'),
                'endsAtLocal'   => $eLocal->format('d/m H:i'),
                'locationName'  => $e->getLocationName(),
                'isAllDay'      => $e->isAllDay(),
                'commune'       => $e->getCommuneCode(),
            ];
        }

        return new JsonResponse($out);
    }

    #[Route('/events/{slug}', name: 'event_show', methods: ['GET'])]
    public function show(string $slug, EM $em): Response
    {
        $event = $em->getRepository(Event::class)->findOneBy(['slug' => $slug]);
        if (!$event || (!$event->isPublished() && !$this->canManage($event))) {
            throw $this->createNotFoundException();
        }

        // Formatages pour l'affichage
        $tz = new \DateTimeZone($event->getTimezone());
        $startsLocal = $event->getStartsAt()->setTimezone($tz);
        $endsLocal   = $event->getEndsAt()->setTimezone($tz);

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            '_show',
            0,
            "nocity");


        return $this->render('pwa/agenda/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'directory'=>'agenda',
            'e'           => $event,
            'startsLocal' => $startsLocal,
            'endsLocal'   => $endsLocal,
            'canManage'   => $this->canManage($event),
            // optionnel: renvoie l’inscription courante du participant pour afficher le bon bouton
            'enrollment'  => $this->getCurrentEnrollment($event, $this->getUserParticipant(), $em) ?? null,
        ]);
    }

    private function canManage(Event $e): bool
    {
        $p = $this->getUserParticipant(); // voir ci-dessous
        return $p && $e->getOrganizer()->getId() === $p->getId();
    }

    private function getUserParticipant(): ?Participant
    {
        // Adapte à ton système: tu as un Participant en session via #[RequireParticipant]
        // Ici on tente depuis la session Symfony (ex: $request->getSession()->get('_participant')).
        $session = $this->get('request_stack')->getSession();
        return $session->get('_participant'); // cast si besoin
    }


    #[Route('/events/{slug}.ics', name: 'event_ics', methods: ['GET'])]
    public function __invoke(string $slug, EM $em, IcsExporter $ics): Response
    {
        $event = $em->getRepository(Event::class)->findOneBy(['slug' => $slug]);
        if (!$event || (!$event->isPublished())) {
            throw $this->createNotFoundException();
        }
        $data = $ics->exportEvent($event);
        return new Response($data, 200, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$event->getSlug().'.ics"',
        ]);
    }

    private function getCurrentEnrollment(Event $e, ?Participant $p, \Doctrine\ORM\EntityManagerInterface $em): ?\App\Entity\Agenda\Enrollment
    {
        if (!$p) return null;
        return $em->getRepository(\App\Entity\Agenda\Enrollment::class)
            ->findOneBy(['event' => $e, 'participant' => $p]);
    }

}
