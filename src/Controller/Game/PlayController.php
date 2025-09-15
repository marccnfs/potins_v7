<?php

namespace App\Controller\Game;

use App\Attribute\RequireParticipant;
use App\Classe\PublicSession;
use App\Entity\Games\MobileLink;
use App\Lib\Links;
use App\Repository\EscapeGameRepository;
use App\Repository\PlaySessionRepository;
use App\Service\MobileLinkManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/play')]
class PlayController extends AbstractController
{
    use PublicSession;
    #[Route('/{slug}', name:'play_entry', methods:['GET'])]
    #[RequireParticipant]
    public function entry(Request $req,EscapeGameRepository $repo,PlaySessionRepository $playSessionRepo, string $slug): Response
    {
        $eg = $repo->findOneBy(['shareSlug'=>$slug, 'published'=>true])
            ?? throw $this->createNotFoundException();

        $topSessions = $playSessionRepo->topForGame($eg, 10);

        if (!$eg->isPublished()) {
            $participant=$this->currentParticipant($req);
            if (!$eg->getOwner() || $eg->getOwner()->getId() !== $participant->getId()) {
                throw $this->createAccessDeniedException();
            }
        }


        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            'entry',
            0,
            "nocity");

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'directory'=>'play',
            'vartwig'=>$vartwig,
            'eg'=>$eg,
            'topSessions'=>$topSessions

        ]);
    }

    #[Route('/{slug}/step/{step}', name:'play_step', methods:['GET'])]
    #[RequireParticipant]
    public function step(Request $req,EscapeGameRepository $repo,MobileLinkManager $mobile, string $slug, int $step): Response
    {
        $participant = $req->attributes->get('_participant');

        $eg = $repo->findOneBy(['shareSlug'=>$slug, 'published'=>true])
            ?? throw $this->createNotFoundException();

        $puzzle = $eg->getPuzzleByStep($step) ?? throw $this->createNotFoundException();
// --- AJOUT SPÉCIFIQUE QR GEO ---
        $extras = [];
        if ($puzzle->getType() === 'qr_geo') {

            $link = $this->em->getRepository(MobileLink::class)->findOneBy([
                'participant' => $participant,
                'escapeGame'  => $eg,
                'step'        => $step,
                'usedAt'      => null,
            ]);

            $expired = $link && $link->getExpiresAt() && $link->getExpiresAt() < new \DateTimeImmutable();
            if (!$link || $expired) {
                $link = $mobile->create($participant, $eg, $step, ttlMinutes: 15);
            }

            $extras = [
                'qr'        => $mobile->buildQrDataUri($link),                  // data:image/png;base64,...
                'token'     => $link->getToken(),
                'expiresAt' => $link->getExpiresAt(),
            ];
        }
        // --- FIN AJOUT ---


        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            'step',
            0,
            "nocity");

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'directory'=>'play',
            'vartwig'=>$vartwig,
            'eg'     => $eg,
            'puzzle' => $puzzle,
            'cfg'    => $puzzle->getConfig() ?? [],
            'step'   => $step,
            'extras' => $extras,
        ]);

    }
    // src/Controller/PlayController.php
    #[Route('/{slug}/the-end', name: 'play_the_end')]
    #[RequireParticipant]
    public function theEnd(EscapeGameRepository $repo, string $slug): Response
    {
        $eg = $repo->findOneBy(['shareSlug'=>$slug, 'published'=>true])
            ?? throw $this->createNotFoundException();

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            'the_end',
            0,
            "nocity");

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'directory'=>'play',
            'vartwig'=>$vartwig,
            'eg'=>$eg,
        ]);

    }


}
