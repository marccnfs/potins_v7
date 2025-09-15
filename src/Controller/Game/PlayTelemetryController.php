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

        $this->em->flush();
        return new JsonResponse(['ok'=>true, 'score'=>$score]);
    }
}
