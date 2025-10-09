<?php

namespace App\Controller\Game\Escape;

use App\Attribute\RequireParticipant;
use App\Classe\UserSessionTrait;
use App\Entity\Games\EscapeGame;
use App\Entity\Games\PlaySession;
use App\Entity\Users\Participant;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Service\Attribute\Required;


class PlayTelemetryController extends AbstractController
{
    use UserSessionTrait;

    private CsrfTokenManagerInterface $csrfTokenManager;

    #[Required]
    public function setCsrfTokenManager(CsrfTokenManagerInterface $csrfTokenManager): void
    {
        $this->csrfTokenManager = $csrfTokenManager;
    }

    private function isCsrfValid(Request $req): bool
    {
        $token = $req->headers->get('X-CSRF-TOKEN', '');
        return $this->csrfTokenManager->isTokenValid(new CsrfToken('play', $token));
    }

    #[Route('/play/{slug}/start', name: 'play_start', methods: ['POST'])]
    #[RequireParticipant]
    public function start(Request $req, EscapeGame $eg): JsonResponse
    {
        if (!$this->isCsrfValid($req)) {
            return new JsonResponse(['ok'=>false], 403);
        }

        $participant = $this->currentParticipant($req);
        $repo = $this->em->getRepository(PlaySession::class);

        $forceRestart = filter_var($req->request->get('restart', false), FILTER_VALIDATE_BOOLEAN);

        if ($forceRestart && $req->hasSession()) {
            $req->getSession()->remove('play_session_id_'.$eg->getId());
            $req->getSession()->remove('play_progress_'.$eg->getId());
        }

        $session = null;
        if (!$forceRestart) {
            $session = $this->sessionFromRequest($req, $eg, $participant);
            if (!$session) {
                $session = $repo->findLatestActiveForParticipant($eg, $participant);
            }
        } else {
            $existing = $repo->findLatestActiveForParticipant($eg, $participant);
            if ($existing) {
                $existing->setEndedAt(new DateTimeImmutable());
                $existing->touch();
            }
        }

        $created = false;
        if (!$session) {
            $session = new PlaySession();
            $session->setEscapeGame($eg);
            $session->setParticipant($participant);
            $this->em->persist($session);
            $created = true;
        }

        $step = (int) $req->request->get('step', 0);
        if ($step > 0) {
            $session->setCurrentStep($step);
        }
        $session->touch();

        $this->em->flush();

        if ($req->hasSession()) {
            $req->getSession()->set('play_session_id_'.$eg->getId(), $session->getId());
        }

        return new JsonResponse([
            'ok'       => true,
            'sid'      => $session->getId(),
            'reused'   => !$created,
            'progress' => $session->getProgressSteps(),
        ]);
    }

    #[Route('/play/{slug}/hint', name: 'play_hint', methods: ['POST'])]
    #[RequireParticipant]
    public function hint(Request $req, EscapeGame $eg): JsonResponse
    {
        if (!$this->isCsrfValid($req)) {
            return new JsonResponse(['ok'=>false], 403);
        }

        $participant = $this->currentParticipant($req);
        $session = $this->sessionFromRequest($req, $eg, $participant);
        if (!$session) {
            return new JsonResponse(['ok'=>false], 400);
        }
        $session->setHintsUsed($session->getHintsUsed()+1);
        $session->touch();

        $this->em->flush();

        return new JsonResponse(['ok'=>true, 'hints'=>$session->getHintsUsed()]);
    }

    #[Route('/play/{slug}/finish', name: 'play_finish', methods: ['POST'])]
    #[RequireParticipant]
    public function finish(Request $req, EscapeGame $eg): JsonResponse
    {
        if (!$this->isCsrfValid($req)) {
            return new JsonResponse(['ok'=>false], 403);
        }

        $participant = $this->currentParticipant($req);
        $session = $this->sessionFromRequest($req, $eg, $participant);
        if (!$session) {
            return new JsonResponse(['ok'=>false], 400);
        }

        $durationMs = (int) $req->request->get('durationMs', 0);

        $session->setDurationMs(max(0, $durationMs));
        $session->setCompleted(true);
        $session->setEndedAt(new DateTimeImmutable());

        $score = max(0, 100 - $session->getHintsUsed() - intdiv($session->getDurationMs(), 30_000));
        $session->setScore($score);

        $totalSteps = max(1, $eg->getPuzzles()->count() ?: 6);
        $session->setProgressSteps(range(1, $totalSteps));
        $session->setCurrentStep($totalSteps);
        $session->touch();

        if ($req->hasSession()) {
            $progress = [];
            for ($i = 1; $i <= $totalSteps; $i++) {
                $progress[$i] = true;
            }
            $req->getSession()->set('play_progress_'.$eg->getId(), $progress);
        }

        $this->em->flush();

        return new JsonResponse(['ok'=>true, 'score'=>$score]);
    }

    #[Route('/play/{slug}/progress', name: 'play_progress', methods: ['POST'])]
    #[RequireParticipant]
    public function progress(Request $req, EscapeGame $eg): JsonResponse
    {
        if (!$this->isCsrfValid($req)) {
            return new JsonResponse(['ok'=>false], 403);
        }

        $step = filter_var($req->request->get('step'), FILTER_VALIDATE_INT);
        if (!$step || $step < 1 || $step > 6) {
            return new JsonResponse(['ok'=>false], 400);
        }

        $participant = $this->currentParticipant($req);
        $sessionEntity = $this->sessionFromRequest($req, $eg, $participant);
        if (!$sessionEntity) {
            return new JsonResponse(['ok'=>false], 400);
        }

        if ($req->hasSession()) {
            $session = $req->getSession();
            $key = 'play_progress_'.$eg->getId();
            $progress = $session->get($key, []);
            if (!\is_array($progress)) {
                $progress = [];
            }
            $progress[$step] = true;
            $session->set($key, $progress);
        }

        $sessionEntity->addProgressStep($step);
        $sessionEntity->setCurrentStep($step);
        $sessionEntity->touch();
        $this->em->flush();

        return new JsonResponse(['ok'=>true, 'steps'=>$sessionEntity->getProgressSteps()]);
    }

    private function sessionFromRequest(Request $req, EscapeGame $eg, Participant $participant): ?PlaySession
    {
        if (!$req->hasSession()) {
            return null;
        }

        $sid = $req->getSession()->get('play_session_id_'.$eg->getId());
        if (!$sid) {
            return null;
        }
        $session = $this->em->getRepository(PlaySession::class)->find($sid);
        if (!$session || !$session->getParticipant() || $session->getParticipant()->getId() !== $participant->getId()) {
            $req->getSession()->remove('play_session_id_'.$eg->getId());
            return null;
        }

        if ($session->isCompleted()) {
            $req->getSession()->remove('play_session_id_'.$eg->getId());
            return null;
        }
        return $session;
    }
}
