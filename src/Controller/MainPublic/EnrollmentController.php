<?php

namespace App\Controller\MainPublic;

use App\Classe\UserSessionTrait;
use App\Entity\Agenda\Enrollment;
use App\Entity\Agenda\Event;
use App\Enum\EventStatus;
use App\Service\ParticipantContext;
use Doctrine\DBAL\LockMode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class EnrollmentController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/events/{slug}/enroll', name: 'event_enroll', methods: ['POST'])]
    public function enroll(string $slug, Request $req,ParticipantContext $participantContext ): RedirectResponse
    {
        $this->denyUnlessCsrf($req, 'enroll_'.$slug);

        $event = $this->em->getRepository(Event::class)->findOneBy(['slug' => $slug]);
        if (!$event || !$event->isPublished() || $event->getStatus() !== EventStatus::SCHEDULED) {
            throw $this->createNotFoundException();
        }

        $p = $participantContext->getParticipantOrFail();

        return $this->em->wrapInTransaction(function () use ($event, $p, $slug) {
            $this->em->lock($event, LockMode::PESSIMISTIC_WRITE);

            $capacity = $event->getCapacity();
            $repo = $this->em->getRepository(Enrollment::class);
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
            $this->em->persist($enr);
            $this->em->flush();

            $this->addFlash('success', $status === 'confirmed' ? 'Inscription confirmée.' : 'Liste d’attente.');
            return $this->redirectToRoute('event_show', ['slug' => $slug]);
        });
    }

    #[Route('/events/{slug}/cancel-enrollment', name: 'event_cancel_enrollment', methods: ['POST'])]
    public function cancel(string $slug, Request $req, ParticipantContext $participantContext): RedirectResponse
    {
        $this->denyUnlessCsrf($req, 'cancel_enrollment_'.$slug);

        $event = $this->em->getRepository(Event::class)->findOneBy(['slug' => $slug]);
        if (!$event) { throw $this->createNotFoundException(); }

        $p = $participantContext->getParticipantOrFail();
        $repo = $this->em->getRepository(Enrollment::class);

        return $this->em->wrapInTransaction(function () use ($event, $p, $repo, $slug) {
            $this->em->lock($event, LockMode::PESSIMISTIC_WRITE);

            $enr = $repo->findOneBy(['event' => $event, 'participant' => $p]);

            if (!$enr) {
                $this->addFlash('info', 'Vous n’êtes pas inscrit(e).');
                return $this->redirectToRoute('event_show', ['slug' => $slug]);
            }
            $this->em->remove($enr);
            $this->em->flush();

            if ($event->getCapacity() !== null) {
                $wait = $repo->findBy(['event' => $event, 'status' => 'waitlist'], ['createdAt' => 'ASC'], 1);
                if ($wait) {
                    $w = $wait[0];
                    $w->setStatus('confirmed');
                    $this->em->flush();
                }
            }

            $this->addFlash('success', 'Inscription annulée.');
            return $this->redirectToRoute('event_show', ['slug' => $slug]);
        });
    }

    private function denyUnlessCsrf(\Symfony\Component\HttpFoundation\Request $req, string $id): void
    {
        $token = $req->request->get('_token');
        if (!$this->isCsrfTokenValid($id, $token)) {
            throw $this->createAccessDeniedException('CSRF invalide.');
        }
    }
}
