<?php

namespace App\Controller\Game\Escape;

use App\Attribute\RequireParticipant;
use App\Classe\UserSessionTrait;
use App\Entity\Games\EscapeGame;
use App\Service\MobileLinkManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayQrGeoController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/play/{slug}/qr/regen/{step}', name: 'play_qr_geo_regen', methods: ['POST'])]
    #[RequireParticipant]
    public function regen(Request $req, EscapeGame $eg, int $step, MobileLinkManager $mobile): Response
    {
        $participant = $req->attributes->get('_participant');
        $puzzle = $eg->getPuzzleByStep($step);
        if (!$puzzle || $puzzle->getType() !== 'qr_geo') {
            return $this->json(['ok'=>false], 404);
        }

        // Invalide d’anciens liens en attente (optionnel)
        // ...

        $link = $mobile->create($participant, $eg, $step, 15);
        $qr   = $mobile->buildQrDataUri($link);

        return $this->json([
            'ok'     => true,
            'token'  => $link->getToken(),
            'qr'     => $qr,
            'expire' => $link->getExpiresAt()?->format(\DateTimeInterface::ATOM),
        ]);
    }

    #[Route('/play/{slug}/step/{step}/qr-answer/{code}', name: 'play_qr_geo_answer', methods: ['GET'])]
    public function answer(EscapeGame $eg, int $step, string $code): Response
    {
        $puzzle = $eg->getPuzzleByStep($step) ?? throw $this->createNotFoundException();
        if ($puzzle->getType() !== 'qr_geo') {
            throw $this->createNotFoundException();
        }

        $cfg = $puzzle->getConfig() ?? [];
        $mode = is_string($cfg['mode'] ?? null) ? $cfg['mode'] : 'geo';
        $qrOnly = is_array($cfg['qrOnly'] ?? null) ? $cfg['qrOnly'] : [];

        if ($mode !== 'qr_only' || ($qrOnly['answerSlug'] ?? null) !== $code) {
            throw $this->createNotFoundException();
        }

        return $this->render('mobile/qr_simple.html.twig', [
            'title'    => $qrOnly['answerTitle'] ?? 'Réponse de l’étape',
            'message'  => $qrOnly['answerBody'] ?? '',
            'subtitle' => $cfg['title'] ?? $eg->getTitresEtapes()[$step] ?? 'Étape',
            'variant'  => 'answer',
        ]);
    }

}
