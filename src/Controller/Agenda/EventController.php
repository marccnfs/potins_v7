<?php

namespace App\Controller\Agenda;

use App\Classe\PublicSession;
use App\Entity\Agenda\Event;
use App\Form\Agenda\EventType;
use App\Lib\Links;
use App\Service\ParticipantContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    use PublicSession;

    #[Route('/events/new', name: 'event_new')]
    public function new(Request $req, ParticipantContext $participantContext): Response
    {
        $p = $participantContext->getParticipantOrFail();

        // valeur par défaut : aujourd’hui 10:00–12:00 (Europe/Paris)
        $nowParis = new \DateTimeImmutable('today 10:00', new \DateTimeZone('Europe/Paris'));
        $form = $this->createForm(EventType::class);

        $form->get('startsAtLocal')->setData($nowParis);
        $form->get('endsAtLocal')->setData($nowParis->modify('+2 hours'));
        $form->get('timezone')->setData('Europe/Paris');

        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            // conversion Local→UTC
            $tz = new \DateTimeZone($form->get('timezone')->getData() ?: 'Europe/Paris');
            $startLocal = $form->get('startsAtLocal')->getData(); // \DateTimeInterface
            $endLocal   = $form->get('endsAtLocal')->getData();

            if (!$startLocal || !$endLocal) {
                $this->addFlash('danger', 'Dates invalides.');
            } else {
                $startUtc = (new \DateTimeImmutable($startLocal->format('Y-m-d H:i:s'), $tz))
                    ->setTimezone(new \DateTimeZone('UTC'));
                $endUtc   = (new \DateTimeImmutable($endLocal->format('Y-m-d H:i:s'), $tz))
                    ->setTimezone(new \DateTimeZone('UTC'));

                $e = new Event(
                    organizer: $p,
                    title:      (string) $form->get('title')->getData(),
                    startsAtUtc:$startUtc,
                    endsAtUtc:  $endUtc,
                    timezone:   (string) $form->get('timezone')->getData(),
                );

                $e->setDescription($form->get('description')->getData());
                $e->setAllDay((bool) $form->get('isAllDay')->getData());
                $e->setLocationName($form->get('locationName')->getData());
                $e->setLocationAddress($form->get('locationAddress')->getData());
                $e->setCapacity($form->get('capacity')->getData());
                $e->setCategory($form->get('category')->getData());
                $e->setVisibility($form->get('visibility')->getData());
                $e->setPublished((bool) $form->get('published')->getData());

                $this->em->persist($e);
                $this->em->flush();

                $this->addFlash('success', 'Événement créé.');
                return $this->redirectToRoute('event_show', ['slug' => $e->getSlug()]);
            }
        }

        return $this->render('pwa/agenda/form.html.twig', [
            'form' => $form,
            'mode' => 'new',
        ]);
    }

    #[Route('/events/{slug}/edit', name: 'event_edit')]
    public function edit(string $slug, Request $req, ParticipantContext $participantContext): Response
    {
        $p = $participantContext->getParticipantOrFail();
        $e = $this->em->getRepository(Event::class)->findOneBy(['slug' => $slug]);
        if (!$e || $e->getOrganizer()->getId() !== $p->getId()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(EventType::class, $e);

        // Pré-remplir les champs *locaux* à partir de l’UTC stocké
        $tz = new \DateTimeZone($e->getTimezone());
        $form->get('startsAtLocal')->setData($e->getStartsAt()->setTimezone($tz));
        $form->get('endsAtLocal')->setData($e->getEndsAt()->setTimezone($tz));

        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $tz = new \DateTimeZone($form->get('timezone')->getData() ?: 'Europe/Paris');
            $startLocal = $form->get('startsAtLocal')->getData();
            $endLocal   = $form->get('endsAtLocal')->getData();

            if (!$startLocal || !$endLocal) {
                $this->addFlash('danger', 'Dates invalides.');
            } else {
                $startUtc = (new \DateTimeImmutable($startLocal->format('Y-m-d H:i:s'), $tz))
                    ->setTimezone(new \DateTimeZone('UTC'));
                $endUtc   = (new \DateTimeImmutable($endLocal->format('Y-m-d H:i:s'), $tz))
                    ->setTimezone(new \DateTimeZone('UTC'));

                $e->setTitle($form->get('title')->getData());
                $e->setDescription($form->get('description')->getData());
                $e->setTimezone($form->get('timezone')->getData());
                $e->setAllDay((bool) $form->get('isAllDay')->getData());
                $e->setLocationName($form->get('locationName')->getData());
                $e->setLocationAddress($form->get('locationAddress')->getData());
                $e->setCapacity($form->get('capacity')->getData());
                $e->setCategory($form->get('category')->getData());
                $e->setVisibility($form->get('visibility')->getData());
                $e->setPublished((bool) $form->get('published')->getData());
                $e->setPeriod($startUtc, $endUtc);
                $e->setCommuneCode($form->get('communeCode')->getData() ?: 'autre');

                $this->em->flush();
                $this->addFlash('success', 'Événement mis à jour.');
                return $this->redirectToRoute('event_show', ['slug' => $e->getSlug()]);
            }
        }

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            '_form',
            0,
            "nocity");


        return $this->render('pwa/agenda/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'directory'=>'agenda',
            'form' => $form,
            'mode' => 'edit',
            'e'    => $e,
        ]);
    }

    #[Route('/events/{slug}/delete', name: 'event_delete', methods: ['POST'])]
    public function delete(string $slug, Request $req, ParticipantContext $participantContext): Response
    {
        $this->denyUnlessCsrf($req, 'delete_'.$slug);

        $p = $participantContext->getParticipantOrFail();
        $e = $this->em->getRepository(Event::class)->findOneBy(['slug' => $slug]);
        if (!$e || $e->getOrganizer()->getId() !== $p->getId()) {
            throw $this->createNotFoundException();
        }

        $this->em->remove($e);
        $this->em->flush();
        $this->addFlash('success', 'Événement supprimé.');

        return $this->redirectToRoute('agenda_index');
    }

    private function denyUnlessCsrf(Request $req, string $id): void
    {
        $token = $req->request->get('_token');
        if (!$this->isCsrfTokenValid($id, $token)) {
            throw $this->createAccessDeniedException('CSRF invalide.');
        }
    }
}
