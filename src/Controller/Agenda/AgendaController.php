<?php

namespace App\Controller\Agenda;

use App\Classe\PublicSession;
use App\Entity\Agenda\Event;
use App\Entity\Users\Participant;
use App\Lib\Links;
use App\Repository\EventRepository;
use App\Service\Agenda\IcsExporter;
use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

     class AgendaController extends AbstractController

{
    use PublicSession;

    #[Route('/agenda', name: 'agenda_index')]
    public function index(Request $req): Response
    {
        $allowedViews = ['month', 'week', 'day'];
        $viewParam = $req->query->get('view');
        $view = \in_array($viewParam, $allowedViews, true) ? $viewParam : 'month';

        $today = new \DateTimeImmutable('today');
        $dateParam = $req->query->get('date');
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dateParam ?? '') ?: false;
        if (!$date || $date->format('Y-m-d') !== $dateParam) {
            $date = $today;
        }

        $vartwig=$this->menuNav->templatePotins(
            '_index',
            "agenda cnfs"
        );


        return $this->render('pwa/agenda/home.html.twig', [
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'directory'=>'agenda',
            'view' => $view,
            'date' => $date,
        ]);
    }

    #[Route('/agenda/feed.json', name: 'agenda_feed', methods: ['GET'])]
    public function feed(Request $req, EventRepository $eventRepository): JsonResponse
    {
        $from = $req->query->get('from'); // YYYY-MM-DD (local Europe/Paris)
        $to   = $req->query->get('to');

        if (!$from || !$to) {
            return new JsonResponse(['error' => 'from/to required'], 400);
        }

        $tzParis = new \DateTimeZone('Europe/Paris');
        $fromDate = \DateTimeImmutable::createFromFormat('Y-m-d', $from, $tzParis);
        $toDate   = \DateTimeImmutable::createFromFormat('Y-m-d', $to, $tzParis);
        if (!$fromDate || $fromDate->format('Y-m-d') !== $from || !$toDate || $toDate->format('Y-m-d') !== $to) {
            return new JsonResponse(['error' => 'invalid from/to'], 400);
        }

        $fromLocal = $fromDate->setTime(0, 0, 0);
        $toLocal   = $toDate->setTime(23, 59, 59);

        // Converti en UTC pour requête
        $fromUtc = $fromLocal->setTimezone(new \DateTimeZone('UTC'));
        $toUtc   = $toLocal->setTimezone(new \DateTimeZone('UTC'));

        $events = $eventRepository->findPublishedInRange(
            $fromUtc,
            $toUtc,
            $req->query->get('category'),
            $req->query->get('commune')
        );

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
                'category'      => $e->getCategory()->value,
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
            '_show',
            'show agenda');


        return $this->render('pwa/agenda/home.html.twig', [
            'replacejs'=>false,
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
        $session = $this->requestStack->getSession();
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
