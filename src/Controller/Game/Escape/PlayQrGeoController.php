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


        $link = $mobile->create($participant, $eg, $step, 15);
        $qr   = $mobile->buildQrDataUri($link);

        return $this->json([
            'ok'     => true,
            'token'  => $link->getToken(),
            'qr'     => $qr,
            'expire' => $link->getExpiresAt()?->format(\DateTimeInterface::ATOM),
        ]);
    }

}
