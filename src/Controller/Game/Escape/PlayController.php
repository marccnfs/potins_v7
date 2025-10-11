<?php

namespace App\Controller\Game\Escape;

use App\Attribute\RequireParticipant;
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

        $totalSteps = max(1, $eg->getPuzzles()->count() ?: 6);
        $httpProgress = $this->loadHttpProgress($req, (int) $eg->getId(), $totalSteps);

        $activeSession = $playSessionRepo->findLatestActiveForParticipant($eg, $participant);
        $recentSessions = $playSessionRepo->findRecentForParticipant($eg, $participant, 5);

        $dbProgress = $activeSession ? $activeSession->getProgressSteps() : [];
        $progressSteps = $dbProgress ?: $httpProgress;
        if ($dbProgress && $httpProgress) {
            $progressSteps = array_values(array_unique(array_merge($dbProgress, $httpProgress)));
        }
        $progressSteps = array_filter($progressSteps, static fn ($step) => $step >= 1 && $step <= $totalSteps);

        sort($progressSteps);
        $doneCount = min(\count($progressSteps), $totalSteps);
        $firstIncomplete = $this->firstIncompleteStep($progressSteps, $totalSteps);
        $resumeStep = $activeSession
            ? $activeSession->getResumeStep($totalSteps)
            : ($firstIncomplete ?? $totalSteps);

        $bestSession = null;
        foreach ($recentSessions as $session) {
            if ($session->isCompleted() && (!$bestSession || $session->getScore() > $bestSession->getScore())) {
                $bestSession = $session;
            }
        }


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
            'totalSteps'    => $totalSteps,
            'activeSession' => $activeSession,
            'recentSessions'=> $recentSessions,
            'bestSession'   => $bestSession,
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
        $totalSteps = max(1, $eg->getPuzzles()->count() ?: 6);
        $forceRestart = $req->query->getBoolean('restart', false);
// --- AJOUT SPÉCIFIQUE QR GEO ---
        $cfg = $puzzle->getConfig() ?? [];
        $extras = [];
        if ($puzzle->getType() === 'qr_geo') {

            $mode = is_string($cfg['mode'] ?? null) ? $cfg['mode'] : 'geo';
            $qrOnly = is_array($cfg['qrOnly'] ?? null) ? $cfg['qrOnly'] : [];
            $ttl = ($mode === 'qr_only' && !empty($qrOnly['noExpiry'])) ? null : 15;


            $link = $this->em->getRepository(MobileLink::class)->findOneBy([
                'participant' => $participant,
                'escapeGame'  => $eg,
                'step'        => $step,
                'usedAt'      => null,
            ]);

            $expired = $link && $link->getExpiresAt() && $link->getExpiresAt() < new \DateTimeImmutable();
            $ttlChanged = $link ? (($ttl === null && $link->getExpiresAt() !== null) || ($ttl !== null && !$link->getExpiresAt())) : false;
            if (!$link || $expired || $ttlChanged) {
                $link = $mobile->create($participant, $eg, $step, ttlMinutes: $ttl);
            }

            $extras = [
                'mode'      => $mode,
                'qr'        => $mobile->buildQrDataUri($link),
                'token'     => $link->getToken(),
                'expiresAt' => $link->getExpiresAt(),
                'noExpiry'  => ($mode === 'qr_only') && !empty($qrOnly['noExpiry']),
            ];

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
            'totalSteps' => $totalSteps,
            'forceRestart' => $forceRestart,
        ]);

    }
    // src/Controller/PlayController.php
    #[Route('/{slug}/the-end', name: 'play_the_end')]
    #[RequireParticipant]
    public function theEnd(Request $req,EscapeGameRepository $repo, PlaySessionRepository $playSessionRepo, string $slug): Response
    {
        $eg = $repo->findOneBy(['shareSlug'=>$slug, 'published'=>true])
            ?? throw $this->createNotFoundException();
        $participant=$this->currentParticipant($req);
        $vartwig=$this->menuNav->templatepotins('the_end',Links::GAMES);
        $total = max(1, $eg->getPuzzles()->count() ?: 6);

        $httpProgress = $this->loadHttpProgress($req, (int) $eg->getId(), $total);
        $activeSession = $playSessionRepo->findLatestActiveForParticipant($eg, $participant);
        $dbProgress = $activeSession ? $activeSession->getProgressSteps() : [];

        if (!$dbProgress) {
            $recent = $playSessionRepo->findRecentForParticipant($eg, $participant, 1);
            $candidate = $recent[0] ?? null;
            if ($candidate) {
                $dbProgress = $candidate->getProgressSteps();
            }
        }

        $progressSteps = $dbProgress ?: $httpProgress;
        if ($dbProgress && $httpProgress) {
            $progressSteps = array_values(array_unique(array_merge($dbProgress, $httpProgress)));
        }
        $progressSteps = array_filter($progressSteps, static fn ($step) => $step >= 1 && $step <= $total);
        sort($progressSteps);

        if (\count($progressSteps) < $total) {
            $redirectStep = $this->firstIncompleteStep($progressSteps, $total) ?? 1;

            return $this->redirectToRoute('play_step', [
                'slug' => $eg->getShareSlug(),
                'step' => $redirectStep,
            ]);
        }
        $fragments = [];
        $missing = [];
        for ($i = 1; $i <= $total; ++$i) {
            $puzzle = $eg->getPuzzleByStep($i);
            $clue = null;
            if ($puzzle) {
                $cfg = $puzzle->getConfig() ?? [];
                $raw = $cfg['finalClue'] ?? null;
                if (\is_string($raw)) {
                    $raw = trim($raw);
                }
                if (\is_string($raw) && $raw !== '') {
                    $clue = $raw;
                }
            }
            if ($clue === null) {
                $missing[] = $i;
            }
            $fragments[$i] = $clue;
        }

        $universe = \is_array($eg->getUniverse()) ? $eg->getUniverse() : [];
        $finale = \is_array($universe['finale'] ?? null) ? $universe['finale'] : [];
        $finalPrompt = \is_string($finale['prompt'] ?? null) ? trim($finale['prompt']) : '';
        $finalReveal = \is_string($finale['reveal'] ?? null) ? trim($finale['reveal']) : '';

        $defaultLabels = [
            1 => 'Cryptex numérique',
            2 => 'QR code géolocalisé',
            3 => 'Puzzle numérique',
            4 => 'Formulaire logique',
            5 => 'Vidéo interactive',
            6 => 'Code HTML minimal',
        ];
        $customTitles = $eg->getTitresEtapes() ?? [];
        $stepLabels = [];
        for ($i = 1; $i <= $total; ++$i) {
            $label = trim((string)($customTitles[$i] ?? ''));
            if ($label === '') {
                $label = $defaultLabels[$i] ?? sprintf('Étape %d', $i);
            }
            $stepLabels[$i] = $label;
        }

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'directory'=>'play',
            'participant'=>$participant,
            'vartwig'=>$vartwig,
            'eg'=>$eg,
            'fragments'=>$fragments,
            'missingFragments'=>$missing,
            'finale'=>[
                'prompt' => $finalPrompt,
                'reveal' => $finalReveal,
            ],
            'stepLabels'=>$stepLabels,
            'totalSteps'=>$total,
        ]);

    }

    /**
     * @return int[]
     */
    private function loadHttpProgress(Request $req, int $gameId, int $totalSteps): array
    {
        if (!$req->hasSession() || $gameId <= 0) {
            return [];
        }

        $session = $req->getSession();
        $stored = $session->get('play_progress_'.$gameId, []);
        if (!\is_array($stored)) {
            return [];
        }

        $progressMap = [];
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
                $progressMap[$step] = true;
            }
        }

        $progress = array_keys($progressMap);
        sort($progress);

        return $progress;
    }

    private function firstIncompleteStep(array $progressSteps, int $totalSteps): ?int
    {
        $totalSteps = max(1, $totalSteps);
        $indexed = [];
        foreach ($progressSteps as $step) {
            if (\is_int($step)) {
                $indexed[$step] = true;
            } elseif (\is_string($step) && ctype_digit($step)) {
                $indexed[(int) $step] = true;
            }
        }

        for ($i = 1; $i <= $totalSteps; ++$i) {
            if (!isset($indexed[$i])) {
                return $i;
            }
        }

        return null;
    }

}
