<?php

namespace App\Controller\Game;

use App\Attribute\RequireParticipant;
use App\Classe\PublicSession;
use App\Classe\UserSessionTrait;
use App\Entity\Games\MobileLink;
use App\Lib\Links;
use App\Repository\EscapeGameRepository;
use App\Repository\PlaySessionRepository;
use App\Service\MobileLinkManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/play')]
class PlayController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/{slug}', name:'play_entry', methods:['GET'])]
    #[RequireParticipant]
    public function entry(Request $req,EscapeGameRepository $repo,PlaySessionRepository $playSessionRepo, string $slug): Response
    {
        $eg = $repo->findOneBy(['shareSlug'=>$slug, 'published'=>true])
            ?? throw $this->createNotFoundException();

        $participant=$this->currentParticipant($req);

        $topSessions = $playSessionRepo->topForGame($eg, 10);

        if (!$eg->isPublished()) {
            $participant=$this->currentParticipant($req);
            if (!$eg->getOwner() || $eg->getOwner()->getId() !== $participant->getId()) {
                throw $this->createAccessDeniedException();
            }
        }

        $totalSteps = 6;
        $progressSteps = [];
        if ($req->hasSession()) {
            $session = $req->getSession();
            $stored = $session->get('play_progress_'.$eg->getId(), []);
            if (\is_array($stored)) {
                foreach ($stored as $key => $value) {
                    if (\is_int($key) || ctype_digit((string) $key)) {
                        $step = (int) $key;
                        $flag = \is_bool($value) ? $value : (bool) $value;
                    } elseif (\is_int($value) || ctype_digit((string) $value)) {
                        $step = (int) $value;
                        $flag = true;
                    } else {
                        continue;
                    }
                    if ($flag && $step >= 1 && $step <= $totalSteps) {
                        $progressSteps[$step] = true;
                    }
                }
            }
        }
        $progressSteps = array_keys($progressSteps);
        sort($progressSteps);
        $doneCount = min(\count($progressSteps), $totalSteps);
        $lastStep = $doneCount ? max($progressSteps) : 0;
        $resumeStep = $doneCount >= $totalSteps ? $totalSteps : max(1, min($totalSteps, $lastStep + 1));

        $vartwig=$this->menuNav->templatepotins('entry',Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'directory'=>'play',
            'vartwig'=>$vartwig,
            'eg'=>$eg,
            'participant'=>$participant,
            'topSessions'=>$topSessions,
            'progressSteps' => $progressSteps,
            'progressCount' => $doneCount,
            'resumeStep'    => $resumeStep,

        ]);
    }

    #[Route('/{slug}/step/{step}', name:'play_step', methods:['GET'])]
    #[RequireParticipant]
    public function step(Request $req,EscapeGameRepository $repo,MobileLinkManager $mobile, string $slug, int $step): Response
    {
        $participant=$this->currentParticipant($req);

        $eg = $repo->findOneBy(['shareSlug'=>$slug, 'published'=>true])
            ?? throw $this->createNotFoundException();

        $puzzle = $eg->getPuzzleByStep($step) ?? throw $this->createNotFoundException();
// --- AJOUT SPÉCIFIQUE QR GEO ---
        $cfg = $puzzle->getConfig() ?? [];
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
            $mode = is_string($cfg['mode'] ?? null) ? $cfg['mode'] : 'geo';

            $extras = [
                'mode'      => $mode,
                'qr'        => $mobile->buildQrDataUri($link),
                'token'     => $link->getToken(),
                'expiresAt' => $link->getExpiresAt(),
            ];

            if ($mode === 'qr_only') {
                $qrOnly = is_array($cfg['qrOnly'] ?? null) ? $cfg['qrOnly'] : [];
                $updated = false;
                if (!isset($qrOnly['answerSlug']) || !is_string($qrOnly['answerSlug']) || $qrOnly['answerSlug'] === '') {
                    $qrOnly['answerSlug'] = bin2hex(random_bytes(5));
                    $cfg['qrOnly'] = $qrOnly;
                    $updated = true;
                }

                if ($updated) {
                    $puzzle->setConfig($cfg);
                    $this->em->flush();
                }

                $answerUrl = $this->generateUrl('play_qr_geo_answer', [
                    'slug' => $slug,
                    'step' => $step,
                    'code' => $qrOnly['answerSlug'],
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                $extras['answerUrl'] = $answerUrl;
                $extras['answerQr'] = $mobile->buildQrForUrl($answerUrl);
            }
        }

        $vartwig=$this->menuNav->templatepotins('step',Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'directory'=>'play',
            'vartwig'=>$vartwig,
            'participant'=>$participant,
            'eg'     => $eg,
            'puzzle' => $puzzle,
            'cfg'    => $cfg,
            'step'   => $step,
            'extras' => $extras,
        ]);

    }
    // src/Controller/PlayController.php
    #[Route('/{slug}/the-end', name: 'play_the_end')]
    #[RequireParticipant]
    public function theEnd(Request $req,EscapeGameRepository $repo, string $slug): Response
    {
        $eg = $repo->findOneBy(['shareSlug'=>$slug, 'published'=>true])
            ?? throw $this->createNotFoundException();
        $participant=$this->currentParticipant($req);
        $vartwig=$this->menuNav->templatepotins('the_end',Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'directory'=>'play',
            'participant'=>$participant,
            'vartwig'=>$vartwig,
            'eg'=>$eg,
        ]);

    }

}
