<?php

namespace App\Service\Games;

use App\Entity\Games\EscapeTeamRun;
use App\Entity\Games\EscapeTeamSession;
use App\Repository\EscapeTeamSessionRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class EscapeTeamProgressService
{
    public function __construct(
        private readonly EscapeTeamSessionRepository $sessionRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function recordStepCompletion(
        EscapeTeamSession $session,
        int $step,
        int $totalSteps = 5,
        ?int $stepDurationMs = null,
        int $hintsUsedDelta = 0,
        array $metadata = [],
    ): EscapeTeamSession {
        $run = $session->getRun();
        if (!$run || $run->getStatus() !== EscapeTeamRun::STATUS_RUNNING) {
            throw new RuntimeException('Le run doit être en cours pour enregistrer une étape.');
        }

        if ($step < 1) {
            throw new RuntimeException('Le numéro d\'étape doit être positif.');
        }

        $totalSteps = max(1, $totalSteps);
        $now = new DateTimeImmutable();

        $stepStates = $session->getStepStates();
        $stepStates[$step] = array_merge([
            'completedAt' => $now->format(DATE_ATOM),
            'durationMs' => $stepDurationMs,
            'hintsUsedDelta' => $hintsUsedDelta,
        ], $metadata);

        $session->setStepStates($stepStates);
        $session->setHintsUsed($session->getHintsUsed() + max(0, $hintsUsedDelta));

        if ($stepDurationMs !== null) {
            $session->setDurationMs($session->getDurationMs() + max(0, $stepDurationMs));
        }

        $nextStep = $step + 1;
        if ($nextStep > $totalSteps) {
            $session->setCurrentStep(null);
            $session->setCompleted(true);
            $session->setEndedAt($session->getEndedAt() ?? $now);
        } else {
            $session->setCurrentStep($nextStep);
        }

        $session->setLastActivityAt($now);

        $this->em->flush();

        return $session;
    }

    public function recordLogicPartCompletion(
        EscapeTeamSession $session,
        int $step,
        string $partKey,
        int $expectedParts = 3,
        int $totalSteps = 5,
        array $metadata = [],
        ?int $stepDurationMs = null,
        int $hintsUsedDelta = 0,
    ): EscapeTeamSession {
        if ($partKey === '') {
            throw new RuntimeException('Une clé de sous-étape est requise.');
        }

        $run = $session->getRun();
        if (!$run || $run->getStatus() !== EscapeTeamRun::STATUS_RUNNING) {
            throw new RuntimeException('Le run doit être en cours pour enregistrer une partie logique.');
        }

        $stepStates = $session->getStepStates();
        $state = $stepStates[$step] ?? [];
        if (($state['completedAt'] ?? null) !== null) {
            return $session; // déjà validé
        }

        $partials = isset($state['partials']) && is_array($state['partials']) ? $state['partials'] : [];
        $partials[$partKey] = true;
        $state['partials'] = $partials;
        $state['partialCount'] = \count($partials);
        $state['partialKeys'] = array_keys($partials);
        $stepStates[$step] = array_merge($state, $metadata);
        $session->setStepStates($stepStates);
        $session->setLastActivityAt(new DateTimeImmutable());

        if (\count($partials) >= max(1, $expectedParts)) {
            return $this->recordStepCompletion(
                $session,
                $step,
                totalSteps: $totalSteps,
                stepDurationMs: $stepDurationMs,
                hintsUsedDelta: $hintsUsedDelta,
                metadata: array_merge($metadata, ['partials' => array_keys($partials)]),
            );
        }

        if ($hintsUsedDelta > 0) {
            $session->setHintsUsed($session->getHintsUsed() + $hintsUsedDelta);
        }

        if ($stepDurationMs !== null) {
            $session->setDurationMs($session->getDurationMs() + max(0, $stepDurationMs));
        }

        $this->em->flush();

        return $session;
    }

    public function consumeHint(EscapeTeamSession $session, int $count = 1): EscapeTeamSession
    {
        $run = $session->getRun();
        if (!$run || $run->getStatus() !== EscapeTeamRun::STATUS_RUNNING) {
            throw new RuntimeException('Le run doit être en cours pour consommer un indice.');
        }

        $session->setHintsUsed($session->getHintsUsed() + max(0, $count));
        $session->setLastActivityAt(new DateTimeImmutable());

        $this->em->flush();

        return $session;
    }

    public function submitFinalAnswer(
        EscapeTeamSession $session,
        string $finalAnswer,
        ?string $secretCode = null,
    ): EscapeTeamSession {
        $run = $session->getRun();
        if (!$run || $run->getStatus() !== EscapeTeamRun::STATUS_RUNNING) {
            throw new RuntimeException('Le run doit être en cours pour valider la phrase finale.');
        }

        $now = new DateTimeImmutable();
        $session->setFinalAnswer($finalAnswer !== '' ? $finalAnswer : null);
        $session->setSecretCode($secretCode !== '' ? $secretCode : null);
        $session->setCompleted(true);
        $session->setEndedAt($session->getEndedAt() ?? $now);
        $session->setCurrentStep(null);
        $session->setLastActivityAt($now);

        $this->em->flush();

        $this->closeRunIfNeeded($run);

        return $session;
    }

    public function buildLiveProgress(EscapeTeamRun $run, int $stepCount = 5): array
    {
        $now = new DateTimeImmutable();
        $sessions = $this->sessionRepository->findForRun($run);

        $timeLimitSeconds = $run->getTimeLimitSeconds();
        $remainingSeconds = null;
        if ($timeLimitSeconds !== null && $run->getStartedAt() !== null) {
            $elapsed = $now->getTimestamp() - $run->getStartedAt()->getTimestamp();
            $remainingSeconds = max(0, $timeLimitSeconds - $elapsed);
        }

        $stepCount = max(1, $stepCount);

        $teams = [];
        foreach ($sessions as $session) {
            $team = $session->getTeam();
            if ($team === null) {
                continue;
            }

            $completedSteps = $session->isCompleted() ? $stepCount : max(0, ($session->getCurrentStep() ?? 1) - 1);
            $progressPercent = (int) round(($completedSteps / $stepCount) * 100);

            $durationMs = $session->getDurationMs();
            if (!$session->isCompleted() && $session->getStartedAt() !== null) {
                $durationMs = max(
                    $durationMs,
                    ($now->getTimestamp() - $session->getStartedAt()->getTimestamp()) * 1000,
                );
            }

            $teams[] = [
                'teamId' => $team->getId(),
                'teamName' => $team->getName(),
                'avatarKey' => $team->getAvatarKey(),
                'color' => $team->getColor(),
                'currentStep' => $session->getCurrentStep(),
                'completedSteps' => $completedSteps,
                'isCompleted' => $session->isCompleted(),
                'progressPercent' => min(100, $progressPercent),
                'hintsUsed' => $session->getHintsUsed(),
                'penalties' => $session->getPenalties(),
                'durationMs' => $durationMs,
                'startedAt' => $session->getStartedAt(),
                'endedAt' => $session->getEndedAt(),
                'lastActivityAt' => $session->getLastActivityAt(),
                'stepStates' => $session->getStepStates(),
            ];
        }

        usort($teams, function (array $a, array $b) {
            if ($a['isCompleted'] !== $b['isCompleted']) {
                return $a['isCompleted'] ? -1 : 1;
            }

            if ($a['completedSteps'] !== $b['completedSteps']) {
                return $b['completedSteps'] <=> $a['completedSteps'];
            }

            return $a['durationMs'] <=> $b['durationMs'];
        });

        return [
            'runId' => $run->getId(),
            'status' => $run->getStatus(),
            'title' => $run->getTitle(),
            'startedAt' => $run->getStartedAt(),
            'timeLimitSeconds' => $timeLimitSeconds,
            'remainingSeconds' => $remainingSeconds,
            'teams' => $teams,
            'stepCount' => $stepCount,
        ];
    }

    public function computeLeaderboard(EscapeTeamRun $run, int $stepCount = 5): array
    {
        $now = new DateTimeImmutable();
        $sessions = $this->sessionRepository->findForRun($run);

        $entries = [];
        foreach ($sessions as $session) {
            $team = $session->getTeam();
            if ($team === null) {
                continue;
            }

            $completedSteps = $session->isCompleted() ? $stepCount : max(0, ($session->getCurrentStep() ?? 1) - 1);
            $durationMs = $session->getDurationMs();
            if (!$session->isCompleted() && $session->getStartedAt() !== null) {
                $durationMs = max(
                    $durationMs,
                    ($now->getTimestamp() - $session->getStartedAt()->getTimestamp()) * 1000,
                );
            }

            $entries[] = [
                'teamId' => $team->getId(),
                'teamName' => $team->getName(),
                'avatarKey' => $team->getAvatarKey(),
                'color' => $team->getColor(),
                'completedSteps' => $completedSteps,
                'isCompleted' => $session->isCompleted(),
                'durationMs' => $durationMs,
                'hintsUsed' => $session->getHintsUsed(),
                'penalties' => $session->getPenalties(),
                'finalAnswer' => $session->getFinalAnswer(),
                'secretCode' => $session->getSecretCode(),
                'endedAt' => $session->getEndedAt(),
            ];
        }

        usort($entries, static function (array $a, array $b): int {
            if ($a['isCompleted'] !== $b['isCompleted']) {
                return $a['isCompleted'] ? -1 : 1;
            }

            if ($a['completedSteps'] !== $b['completedSteps']) {
                return $b['completedSteps'] <=> $a['completedSteps'];
            }

            $durationCmp = $a['durationMs'] <=> $b['durationMs'];
            if ($durationCmp !== 0) {
                return $durationCmp;
            }

            $penaltyCmp = $a['penalties'] <=> $b['penalties'];
            if ($penaltyCmp !== 0) {
                return $penaltyCmp;
            }

            return $a['hintsUsed'] <=> $b['hintsUsed'];
        });

        return [
            'runId' => $run->getId(),
            'title' => $run->getTitle(),
            'status' => $run->getStatus(),
            'endedAt' => $run->getEndedAt(),
            'stepCount' => $stepCount,
            'entries' => $entries,
        ];
    }

    private function closeRunIfNeeded(EscapeTeamRun $run): void
    {
        if ($run->getStatus() === EscapeTeamRun::STATUS_ENDED) {
            return;
        }

        foreach ($this->sessionRepository->findForRun($run) as $session) {
            if (!$session->isCompleted()) {
                return;
            }
        }

        $run->setStatus(EscapeTeamRun::STATUS_ENDED);
        $run->setEndedAt($run->getEndedAt() ?? new DateTimeImmutable());
        $this->em->flush();
    }
}
