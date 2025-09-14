<?php

namespace App\Controller\Game;

// src/Controller/MobileEntryController.php


use App\Classe\PublicSession;
use App\Entity\Games\MobileLink;
use App\Entity\Games\EscapeGame;
use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MobileEntryController extends AbstractController
{

    use PublicSession;

    #[Route('/m/{token}', name: 'mobile_entry', methods: ['GET'])]
    public function entry(string $token): Response
    {
        $link = $this->em->getRepository(MobileLink::class)->findOneBy(['token'=>$token]);
        if (!$link || ($link->getExpiresAt() && $link->getExpiresAt() < new \DateTimeImmutable()) || $link->getUsedAt()) {
            return $this->render('mobile/invalid.html.twig');
        }

        // On rend une page mobile minimaliste qui va faire navigator.geolocation + POST /m/{token}/verify
        return $this->render('mobile/qr_geo.html.twig', ['link'=>$link]);
    }

    #[Route('/m/{token}/verify', name: 'mobile_verify', methods: ['POST'])]
    public function verify(Request $req, string $token): Response
    {
        $link = $this->em->getRepository(MobileLink::class)->findOneBy(['token'=>$token]);
        if (!$link || $link->getUsedAt()) return $this->json(['ok'=>false], 400);

        /** @var EscapeGame $eg */
        $eg = $link->getEscapeGame();
        $puzzle = $eg->getPuzzleByStep($link->getStep());
        if (!$puzzle) return $this->json(['ok'=>false], 400);

        $cfg = $puzzle->getConfig() ?? [];
        $target = $cfg['target'] ?? ['lat'=>0,'lng'=>0];
        $radius = (int)($cfg['radiusMeters'] ?? 150);

        $lat = (float) $req->request->get('lat');
        $lng = (float) $req->request->get('lng');

        // distance haversine
        $d = self::distanceMeters($lat, $lng, (float)$target['lat'], (float)$target['lng']);

        if ($d <= $radius) {
            $link->setUsedAt(new \DateTimeImmutable());
            $this->em->flush();

            // Ici tu peux aussi marquer l’étape comme "solved" côté serveur si tu as un statut
            return $this->json(['ok'=>true, 'distance'=>$d]);
        }

        return $this->json(['ok'=>false, 'distance'=>$d], 200);
    }

    #[Route('/m/{token}/status', name: 'mobile_link_status', methods: ['GET'])]
    public function status(string $token): Response
    {
        $link = $this->em->getRepository(MobileLink::class)->findOneBy(['token'=>$token]);
        if (!$link) return $this->json(['status'=>'unknown'], 404);
        return $this->json(['status'=>$link->getUsedAt() ? 'used' : 'pending']);
    }

    private static function distanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float {
        $R = 6371000;
        $dLat = deg2rad($lat2-$lat1);
        $dLon = deg2rad($lon2-$lon1);
        $a = sin($dLat/2)**2 + cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLon/2)**2;
        return 2 * $R * asin(min(1, sqrt($a)));
    }
}
