<?php

namespace App\Controller\Game;

use App\Attribute\RequireParticipant;
use App\Classe\PublicSession;
use App\Entity\Games\EscapeGame;
use App\Entity\Games\PlaySession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Service\Attribute\Required;


class PlayTelemetryController extends AbstractController
{
    use PublicSession;

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

        $participant=$this->currentParticipant($req);

        // On peut autoriser 1 session “ouverte” par participant/EG ; sinon on en crée une nouvelle
        $session = new PlaySession();
        $session->setEscapeGame($eg);
        $session->setParticipant($participant);
        $this->em->persist($session);
        $this->em->flush();

        $step = (int) $req->request->get('step', 0);
        if ($req->hasSession() && $step <= 1) {
            $req->getSession()->remove('play_progress_'.$eg->getId());
        }

        // On mémorise l’id en session HTTP pour les prochains POST
        $req->getSession()->set('play_session_id_'.$eg->getId(), $session->getId());

        return new JsonResponse(['ok'=>true, 'sid'=>$session->getId()]);
    }

    #[Route('/play/{slug}/hint', name: 'play_hint', methods: ['POST'])]
    #[RequireParticipant]
    public function hint(Request $req, EscapeGame $eg): JsonResponse
    {
        if (!$this->isCsrfValid($req)) {
            return new JsonResponse(['ok'=>false], 403);
        }

        $sid = $req->getSession()->get('play_session_id_'.$eg->getId());
        if (!$sid) return new JsonResponse(['ok'=>false], 400);

        $ps = $this->em->getRepository(PlaySession::class)->find($sid);
        if (!$ps) return new JsonResponse(['ok'=>false], 400);

        $ps->setHintsUsed($ps->getHintsUsed()+1);
        $this->em->flush();

        return new JsonResponse(['ok'=>true, 'hints'=>$ps->getHintsUsed()]);
    }

    #[Route('/play/{slug}/finish', name: 'play_finish', methods: ['POST'])]
    #[RequireParticipant]
    public function finish(Request $req, EscapeGame $eg): JsonResponse
    {
        if (!$this->isCsrfValid($req)) {
            return new JsonResponse(['ok'=>false], 403);
        }

        $sid = $req->getSession()->get('play_session_id_'.$eg->getId());
        if (!$sid) return new JsonResponse(['ok'=>false], 400);

        $ps = $this->em->getRepository(PlaySession::class)->find($sid);
        if (!$ps) return new JsonResponse(['ok'=>false], 400);

        $durationMs = (int) $req->request->get('durationMs', 0);
        $ps->setDurationMs(max(0,$durationMs));
        $ps->setCompleted(true);
        $ps->setEndedAt(new \DateTimeImmutable());

        $score = max(0, 100 - $ps->getHintsUsed() - intdiv($ps->getDurationMs(), 30_000));
        $ps->setScore($score);

        if ($req->hasSession()) {
            $progress = [];
            for ($i=1; $i<=6; $i++) {
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

        if (!$req->hasSession()) {
            return new JsonResponse(['ok'=>false], 500);
        }

        $session = $req->getSession();
        $key = 'play_progress_'.$eg->getId();
        $progress = $session->get($key, []);
        if (!\is_array($progress)) {
            $progress = [];
        }
        $progress[(int) $step] = true;
        $session->set($key, $progress);

        $steps = array_keys(array_filter($progress, static fn($v) => (bool) $v));
        sort($steps);

        return new JsonResponse(['ok'=>true, 'steps'=>$steps]);
    }
}
