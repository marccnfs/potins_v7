<?php

namespace App\Controller\Game\Escape;

use App\Attribute\RequireParticipant;
use App\Classe\UserSessionTrait;
use App\Entity\Games\EscapeGame;
use App\Entity\Games\MobileLink;
use App\Entity\Games\PlaySession;
use App\Entity\Users\Participant;
use App\Lib\Links;
use App\Repository\EscapeGameRepository;
use App\Repository\PlaySessionRepository;
use App\Service\MobileLinkManager;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/play')]
class PlayController extends AbstractController
{
    private const HTTP_PROGRESS_TTL = 604800; // 7 days
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
        //$httpProgress = $this->loadHttpProgress($req, (int) $eg->getId(), $totalSteps);

        $activeSession = $playSessionRepo->findLatestActiveForParticipant($eg, $participant);
        $recentSessions = $playSessionRepo->findRecentForParticipant($eg, $participant, 5);

        $httpProgress = $this->loadHttpProgress($req, (int) $eg->getId(), $totalSteps, $activeSession);

        $dbProgress = $activeSession ? $activeSession->getProgressSteps() : [];
        $progressSteps = $this->mergeProgressSteps($totalSteps, $dbProgress, $httpProgress);

        if ($activeSession && $this->em) {
            $this->synchronizeSessionProgress($activeSession, $progressSteps, $totalSteps);
        }

        $doneCount = min(\count($progressSteps), $totalSteps);
        $firstIncomplete = $this->firstIncompleteStep($progressSteps, $totalSteps);
        $resumeStep = $firstIncomplete ?? $totalSteps;
        $maxCompleted = $this->maxProgressStep($progressSteps);
        $unlockedStep = max(1, max($resumeStep, $maxCompleted));

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
            'unlockedStep'  => $unlockedStep,
        ]);
    }

    #[Route('/{slug}/step/{step}', name:'play_step', methods:['GET'])]
    #[RequireParticipant]
    public function step(Request $req,EscapeGameRepository $repo,PlaySessionRepository $playSessionRepo,MobileLinkManager $mobile, string $slug, int $step): Response
    {
        $participant=$this->currentParticipant($req);

        $eg = $repo->findOneBy(['shareSlug'=>$slug, 'published'=>true])
            ?? throw $this->createNotFoundException();

        $puzzle = $eg->getPuzzleByStep($step) ?? throw $this->createNotFoundException();
        $totalSteps = max(1, $eg->getPuzzles()->count() ?: 6);
        $forceRestart = $req->query->getBoolean('restart', false);
        $restartFlagKey = 'play_restart_processed_'.$eg->getId();
        $restartHandled = false;
        if ($req->hasSession()) {
            $sessionStore = $req->getSession();
            if ($sessionStore->has($restartFlagKey)) {
                $restartHandled = (bool) $sessionStore->get($restartFlagKey);
                $sessionStore->remove($restartFlagKey);
            }
        }

        if ($restartHandled) {
            $forceRestart = false;
        } elseif ($forceRestart) {
            $this->resetHttpSessionState($req, $eg);
        }
        $activeSession = $playSessionRepo->findLatestActiveForParticipant($eg, $participant);
        $activeSession = $this->ensureActivePlaySession(
            $req,
            $eg,
            $participant,
            $playSessionRepo,
            $forceRestart,
            $step,
            $activeSession,
        );
        $httpProgress = $this->loadHttpProgress($req, (int) $eg->getId(), $totalSteps, $activeSession);
        $dbProgress = $activeSession ? $activeSession->getProgressSteps() : [];
        $progressSteps = $this->mergeProgressSteps($totalSteps, $dbProgress, $httpProgress);

        if ($activeSession && $this->em) {
            $this->synchronizeSessionProgress($activeSession, $progressSteps, $totalSteps);
        }

        $completedCount = \count($progressSteps);
        $firstIncomplete = $this->firstIncompleteStep($progressSteps, $totalSteps);
        $resumeStep = $firstIncomplete ?? $totalSteps;
        $maxCompleted = $this->maxProgressStep($progressSteps);
        $unlockedStep = max(1, max($resumeStep, $maxCompleted));

        if ($step > $unlockedStep) {
            return $this->redirectToRoute('play_step', [
                'slug' => $eg->getShareSlug(),
                'step' => $resumeStep,
            ]);
        }

        $this->updateHttpProgressStore($req, $eg, $progressSteps, $resumeStep);


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
            'progressSteps' => $progressSteps,
            'resumeStep' => $resumeStep,
            'completedCount' => $completedCount,
            'unlockedStep' => $unlockedStep,
        ]);

    }


    private function ensureActivePlaySession(
        Request $req,
        EscapeGame $game,
        Participant $participant,
        PlaySessionRepository $repo,
        bool $forceRestart,
        int $step,
        ?PlaySession $existing,
    ): ?PlaySession {
        if (!$this->em) {
            return $existing;
        }

        $sessionStore = $req->hasSession() ? $req->getSession() : null;
        $sessionKey = 'play_session_id_'.$game->getId();
        $progressKey = 'play_progress_'.$game->getId();
        $toFlush = [];

        if ($existing && $this->isSessionStale($existing)) {
            $existing->setEndedAt(new DateTimeImmutable());
            $existing->touch();
            $toFlush[] = $existing;
            $existing = null;
            if ($sessionStore) {
                $sessionStore->remove($sessionKey);
                $sessionStore->remove($progressKey);
            }
        }

        if ($forceRestart && $sessionStore) {
            $sessionStore->remove($sessionKey);
            $sessionStore->remove($progressKey);
        }

        $sessionEntity = $existing;

        if (!$forceRestart && !$sessionEntity && $sessionStore) {
            $storedId = $sessionStore->get($sessionKey);
            if (\is_scalar($storedId)) {
                $candidate = $repo->find((int) $storedId);
                if (
                    $candidate
                    && !$candidate->isCompleted()
                    && $candidate->getEscapeGame()?->getId() === $game->getId()
                    && $candidate->getParticipant()?->getId() === $participant->getId()
                ) {
                    if ($this->isSessionStale($candidate)) {
                        $candidate->setEndedAt(new DateTimeImmutable());
                        $candidate->touch();
                        $toFlush[] = $candidate;
                        $sessionStore->remove($sessionKey);
                        $sessionStore->remove($progressKey);
                    } else {
                        $sessionEntity = $candidate;
                    }
                }
            }
        }

        $needsFlush = false;

        if ($forceRestart && $sessionEntity) {
            $sessionEntity->setEndedAt(new DateTimeImmutable());
            $sessionEntity->touch();
            $toFlush[] = $sessionEntity;
            $sessionEntity = null;
        }

        if (!$sessionEntity) {
            $sessionEntity = new PlaySession();
            $sessionEntity->setEscapeGame($game);
            $sessionEntity->setParticipant($participant);
            if ($step > 0) {
                $sessionEntity->setCurrentStep($step);
            }
            $sessionEntity->touch();
            $this->em->persist($sessionEntity);
            $toFlush[] = $sessionEntity;
        } elseif ($step > 0 && $sessionEntity->getCurrentStep() !== $step) {
            $sessionEntity->setCurrentStep($step);
            $sessionEntity->touch();
            $needsFlush = true;
        }

        if ($sessionEntity && $needsFlush) {
            $this->em->persist($sessionEntity);
            $toFlush[] = $sessionEntity;
        }

        if (!empty($toFlush)) {
            $this->em->flush();
        }

        if ($sessionStore && $sessionEntity) {
            $sessionStore->set($sessionKey, $sessionEntity->getId());

            if ($step > 0) {
                $progress = $sessionStore->get($progressKey, []);
                if (!\is_array($progress)) {
                    $progress = [];
                }

                $progress['_current'] = $step;
                $progress['_ts'] = time();
                $sessionStore->set($progressKey, $progress);
            } elseif (!$sessionStore->has($progressKey)) {
                $sessionStore->set($progressKey, ['_ts' => time()]);
            } else {
                $progress = $sessionStore->get($progressKey);
                if (\is_array($progress)) {
                    $progress['_ts'] = time();
                    $sessionStore->set($progressKey, $progress);
                }
            }
        }

        return $sessionEntity;
    }

    #[Route('/{slug}/the-end', name: 'play_the_end')]
    #[RequireParticipant]
    public function theEnd(Request $req,EscapeGameRepository $repo, PlaySessionRepository $playSessionRepo, string $slug): Response
    {
        $eg = $repo->findOneBy(['shareSlug'=>$slug, 'published'=>true])
            ?? throw $this->createNotFoundException();
        $participant=$this->currentParticipant($req);
        $vartwig=$this->menuNav->templatepotins('the_end',Links::GAMES);
        $total = max(1, $eg->getPuzzles()->count() ?: 6);

        $activeSession = $playSessionRepo->findLatestActiveForParticipant($eg, $participant);
        $httpProgress = $this->loadHttpProgress($req, (int) $eg->getId(), $total, $activeSession);
        $dbProgress = $activeSession ? $activeSession->getProgressSteps() : [];

        if (!$dbProgress) {
            $recent = $playSessionRepo->findRecentForParticipant($eg, $participant, 1);
            $candidate = $recent[0] ?? null;
            if ($candidate) {
                $dbProgress = $candidate->getProgressSteps();
            }
        }

        $progressSteps = $this->mergeProgressSteps($total, $dbProgress, $httpProgress);

        $maxReached = max(
            $this->maxProgressStep($progressSteps),
            $this->maxProgressStep($httpProgress),
            $activeSession?->getCurrentStep() ?? 0,
        );

        if ($maxReached >= $total && \count($progressSteps) < $total) {
            $progressSteps = range(1, $total);

            if ($activeSession && $this->em) {
                $existingSteps = $activeSession->getProgressSteps();
                $needsUpdate = $existingSteps !== $progressSteps || $activeSession->getCurrentStep() !== $total;

                if ($needsUpdate) {
                    $activeSession->setProgressSteps($progressSteps);
                    $activeSession->setCurrentStep($total);
                    $activeSession->touch();
                    $this->em->persist($activeSession);
                    $this->em->flush();
                }
            }
        }
        $resumeStep = $this->firstIncompleteStep($progressSteps, $total) ?? $total;
        $this->updateHttpProgressStore($req, $eg, $progressSteps, $resumeStep);

        if (\count($progressSteps) < $total) {


            return $this->redirectToRoute('play_step', [
                'slug' => $eg->getShareSlug(),
                'step' => max(1, $resumeStep),
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
    private function loadHttpProgress(Request $req, int $gameId, int $totalSteps, ?PlaySession $activeSession = null): array
    {
        if (!$req->hasSession() || $gameId <= 0) {
            return [];
        }

        $session = $req->getSession();
        $progressKey = 'play_progress_'.$gameId;
        $sessionKey = 'play_session_id_'.$gameId;
        $stored = $session->get($progressKey, []);$stored = $session->get('play_progress_'.$gameId, []);
        if (!\is_array($stored)) {
            return [];
        }

        $timestamp = null;
        if (isset($stored['_ts'])) {
            $rawTs = $stored['_ts'];
            if (\is_int($rawTs)) {
                $timestamp = $rawTs;
            } elseif (\is_string($rawTs) && ctype_digit($rawTs)) {
                $timestamp = (int) $rawTs;
            }
        }

        if ($timestamp !== null && $timestamp <= time() - self::HTTP_PROGRESS_TTL) {
            $session->remove($progressKey);
            $session->remove($sessionKey);
            return [];
        }

        if ($activeSession && $this->isSessionStale($activeSession)) {
            $session->remove($progressKey);
            $session->remove($sessionKey);
            return [];
        }

        $progressMap = [];
        foreach ($stored as $key => $value) {
            if (\is_string($key) && \str_starts_with($key, '_')) {
                continue;
            }
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

    private function resetHttpSessionState(Request $req, EscapeGame $game): void
    {
        if (!$req->hasSession()) {
            return;
        }

        $session = $req->getSession();
        $session->remove('play_session_id_'.$game->getId());
        $session->remove('play_progress_'.$game->getId());
    }

    private function isSessionStale(PlaySession $session): bool
    {
        $updatedAt = $session->getUpdatedAt() ?? $session->getCreatedAt();
        if (!$updatedAt) {
            return false;
        }

        return $updatedAt->getTimestamp() <= time() - self::HTTP_PROGRESS_TTL;
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

    private function maxProgressStep(array $steps): int
    {
        $max = 0;
        foreach ($steps as $step) {
            if (\is_int($step)) {
                $max = max($max, $step);
                continue;
            }

            if (\is_string($step) && ctype_digit($step)) {
                $max = max($max, (int) $step);
            }
        }

        return $max;
    }

    /**
     * @param array<int|string, mixed> $steps
     *
     * @return int[]
     */
    private function normalizeProgressSteps(array $steps, int $totalSteps): array
    {
        $totalSteps = max(1, $totalSteps);
        $normalized = [];

        foreach ($steps as $key => $value) {
            if (\is_int($value) || (\is_string($value) && ctype_digit($value))) {
                $step = (int) $value;
                if ($step >= 1 && $step <= $totalSteps) {
                    $normalized[$step] = true;
                }

                continue;
            }

            if (!\is_int($key) && !(\is_string($key) && ctype_digit($key))) {
                continue;
            }

            $flag = \is_bool($value) ? $value : (bool) $value;
            if (!$flag) {
                continue;
            }

            $step = (int) $key;
            if ($step >= 1 && $step <= $totalSteps) {
                $normalized[$step] = true;
            }
        }

        $result = array_keys($normalized);
        sort($result);

        return $result;
    }

    /**
     * @param array<int|string, mixed> ...$sources
     *
     * @return int[]
     */
    private function mergeProgressSteps(int $totalSteps, array ...$sources): array
    {
        $merged = [];

        foreach ($sources as $source) {
            if (empty($source)) {
                continue;
            }

            foreach ($this->normalizeProgressSteps($source, $totalSteps) as $step) {
                $merged[$step] = true;
            }
        }

        $result = array_keys($merged);
        sort($result);

        return $this->sequentializeProgress($result, $totalSteps);
    }

    private function synchronizeSessionProgress(PlaySession $session, array $progressSteps, int $totalSteps): void
    {
        $normalized = $this->sequentializeProgress($progressSteps, $totalSteps);
        $needsFlush = false;

        if ($session->getProgressSteps() !== $normalized) {
            $session->setProgressSteps($normalized);
            $needsFlush = true;
        }

        $resumeStep = $this->firstIncompleteStep($normalized, $totalSteps);
        if ($resumeStep === null) {
            $resumeStep = max(1, $totalSteps);
        }

        if ($session->getCurrentStep() !== $resumeStep) {
            $session->setCurrentStep($resumeStep);
            $needsFlush = true;
        }

        if ($needsFlush) {
            $session->touch();
            $this->em?->persist($session);
            $this->em?->flush();
        }
    }
    /**
     * @param array<int|string> $steps
     *
     * @return int[]
     */
    private function sequentializeProgress(array $steps, int $totalSteps): array
    {
        $normalized = $this->normalizeProgressSteps($steps, $totalSteps);
        $expected = 1;
        $sequential = [];

        foreach ($normalized as $step) {
            if ($step === $expected) {
                $sequential[] = $step;
                ++$expected;
            } elseif ($step < $expected) {
                continue;
            } else {
                break;
            }
        }

        return $sequential;
    }

    private function updateHttpProgressStore(Request $req, EscapeGame $game, array $progressSteps, int $resumeStep): void
    {
        if (!$req->hasSession()) {
            return;
        }

        $session = $req->getSession();
        $key = 'play_progress_'.$game->getId();

        $totalSteps = max(1, ($game->getPuzzles()->count() ?: 6));

        $payload = [];
        foreach ($progressSteps as $step) {
            if (\is_int($step)) {
                $normalized = $step;
            } elseif (\is_string($step) && ctype_digit($step)) {
                $normalized = (int) $step;
            } else {
                continue;
            }

            if ($normalized < 1 || $normalized > $totalSteps) {
                continue;
            }

            $payload[$normalized] = true;
        }

        $payload['_current'] = max(1, min($resumeStep, $totalSteps));
        $payload['_ts'] = time();

        $session->set($key, $payload);
    }


}
