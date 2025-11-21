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
}
