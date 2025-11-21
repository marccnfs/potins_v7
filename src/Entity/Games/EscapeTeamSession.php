<?php

namespace App\Entity\Games;

use App\Repository\EscapeTeamSessionRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EscapeTeamSessionRepository::class)]
#[ORM\Table(name: 'aff_escape_team_session')]
class EscapeTeamSession
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: EscapeTeamRun::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?EscapeTeamRun $run = null;

    #[ORM\OneToOne(inversedBy: 'session', targetEntity: EscapeTeam::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?EscapeTeam $team = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $progress = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $stepStates = [];

    #[ORM\Column(type: 'integer')]
    private int $hintsUsed = 0;

    #[ORM\Column(type: 'integer')]
    private int $durationMs = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $currentStep = null;

    #[ORM\Column(type: 'boolean')]
    private bool $completed = false;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $penalties = 0;

    #[ORM\Column(length: 190, nullable: true)]
    private ?string $finalAnswer = null;

    #[ORM\Column(length: 190, nullable: true)]
    private ?string $secretCode = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $endedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastActivityAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRun(): ?EscapeTeamRun
    {
        return $this->run;
    }

    public function setRun(EscapeTeamRun $run): static
    {
        $this->run = $run;

        return $this;
    }

    public function getTeam(): ?EscapeTeam
    {
        return $this->team;
    }

    public function setTeam(EscapeTeam $team): static
    {
        $this->team = $team;

        return $this;
    }

    public function getProgress(): array
    {
        return $this->progress ?? [];
    }

    public function setProgress(array $progress): static
    {
        $this->progress = $progress;

        return $this;
    }

    public function getStepStates(): array
    {
        return $this->stepStates ?? [];
    }

    public function setStepStates(array $stepStates): static
    {
        $this->stepStates = $stepStates;

        return $this;
    }

    public function getHintsUsed(): int
    {
        return $this->hintsUsed;
    }

    public function setHintsUsed(int $hintsUsed): static
    {
        $this->hintsUsed = max(0, $hintsUsed);

        return $this;
    }

    public function getDurationMs(): int
    {
        return $this->durationMs;
    }

    public function setDurationMs(int $durationMs): static
    {
        $this->durationMs = max(0, $durationMs);

        return $this;
    }

    public function getCurrentStep(): ?int
    {
        return $this->currentStep;
    }

    public function setCurrentStep(?int $currentStep): static
    {
        $this->currentStep = $currentStep;

        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): static
    {
        $this->completed = $completed;

        return $this;
    }

    public function getPenalties(): int
    {
        return $this->penalties;
    }

    public function setPenalties(int $penalties): static
    {
        $this->penalties = max(0, $penalties);

        return $this;
    }

    public function getFinalAnswer(): ?string
    {
        return $this->finalAnswer;
    }

    public function setFinalAnswer(?string $finalAnswer): static
    {
        $this->finalAnswer = $finalAnswer;

        return $this;
    }

    public function getSecretCode(): ?string
    {
        return $this->secretCode;
    }

    public function setSecretCode(?string $secretCode): static
    {
        $this->secretCode = $secretCode;

        return $this;
    }

    public function getStartedAt(): ?DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getLastActivityAt(): ?DateTimeImmutable
    {
        return $this->lastActivityAt;
    }

    public function setLastActivityAt(?DateTimeImmutable $lastActivityAt): static
    {
        $this->lastActivityAt = $lastActivityAt;

        return $this;
    }
}
