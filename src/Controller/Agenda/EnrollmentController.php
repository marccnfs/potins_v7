<?php

namespace App\Controller\Agenda;

use App\Entity\Agenda\Enrollment;
use App\Entity\Agenda\Event;
use App\Entity\Users\Participant;
use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class EnrollmentController extends AbstractController
{
    #[Route('/events/{slug}/enroll', name: 'event_enroll', methods: ['POST'])]
    public function enroll(string $slug, Request $req, EM $em): RedirectResponse
    {
        $this->denyUnlessCsrf($req, 'enroll_'.$slug);

        $event = $em->getRepository(Event::class)->findOneBy(['slug' => $slug]);
        if (!$event || !$event->isPublished() || $event->getStatus() !== 'scheduled') {
            throw $this->createNotFoundException();
        }
        $p = $this->getParticipantOrFail();

        // capacité
        $capacity = $event->getCapacity();
        $repo = $em->getRepository(Enrollment::class);
        $confirmedCount = $repo->count(['event' => $event, 'status' => 'confirmed']);

        $status = 'confirmed';
        if ($capacity !== null && $confirmedCount >= $capacity) {
            $status = 'waitlist';
        }

        $existing = $repo->findOneBy(['event' => $event, 'participant' => $p]);
        if ($existing) {
            $this->addFlash('info', 'Déjà inscrit(e).');
            return $this->redirectToRoute('event_show', ['slug' => $slug]);
        }

        $enr = new Enrollment($event, $p, $status);
        $em->persist($enr);
        $em->flush();

        $this->addFlash('success', $status === 'confirmed' ? 'Inscription confirmée.' : 'Liste d’attente.');
        return $this->redirectToRoute('event_show', ['slug' => $slug]);
    }

    #[Route('/events/{slug}/cancel-enrollment', name: 'event_cancel_enrollment', methods: ['POST'])]
    public function cancel(string $slug, Request $req, EM $em): RedirectResponse
    {
        $this->denyUnlessCsrf($req, 'cancel_enrollment_'.$slug);

        $event = $em->getRepository(Event::class)->findOneBy(['slug' => $slug]);
        if (!$event) { throw $this->createNotFoundException(); }

        $p = $this->getParticipantOrFail();
        $repo = $em->getRepository(Enrollment::class);
        $enr = $repo->findOneBy(['event' => $event, 'participant' => $p]);

        if (!$enr) {
            $this->addFlash('info', 'Vous n’êtes pas inscrit(e).');
            return $this->redirectToRoute('event_show', ['slug' => $slug]);
        }

        $em->remove($enr);
        $em->flush();

        // Promotion premier en liste d’attente si capacité
        if ($event->getCapacity() !== null) {
            $wait = $repo->findBy(['event' => $event, 'status' => 'waitlist'], ['createdAt' => 'ASC'], 1);
            if ($wait) {
                $w = $wait[0];
                $w->setStatus('confirmed');
                $em->flush();
            }
        }

        $this->addFlash('success', 'Inscription annulée.');
        return $this->redirectToRoute('event_show', ['slug' => $slug]);
    }

    private function getParticipantOrFail(): Participant
    {
        /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $s */
        $s = $this->get('request_stack')->getSession();
        $p = $s->get('_participant');
        if (!$p instanceof Participant) {
            throw $this->createAccessDeniedException('Participant requis.');
        }
        return $p;
    }

    private function denyUnlessCsrf(\Symfony\Component\HttpFoundation\Request $req, string $id): void
    {
        $token = $req->request->get('_token');
        if (!$this->isCsrfTokenValid($id, $token)) {
            throw $this->createAccessDeniedException('CSRF invalide.');
        }
    }
}
