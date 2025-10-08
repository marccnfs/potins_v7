<?php

namespace App\Entity\Games;

use App\Entity\Users\Participant;
use App\Repository\PlaySessionRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaySessionRepository::class)]
#[ORM\Table(name:"aff_playsession")]
#[ORM\Index(columns: ['created_at'])]
#[ORM\Index(columns: ['score'])]
class PlaySession
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: EscapeGame::class, inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EscapeGame $escapeGame = null;

    #[ORM\ManyToOne(targetEntity: Participant::class, inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $participant = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $endedAt = null;

    #[ORM\Column(type: 'integer')]
    private int $hintsUsed = 0;

    #[ORM\Column(type: 'integer')]
    private int $durationMs = 0; // total

    #[ORM\Column(type: 'boolean')]
    private bool $completed = false;

    #[ORM\Column(type: 'integer')]
    private int $score = 0;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $progressSteps = [];

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $currentStep = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    public function getHintsUsed(): ?int
    {
        return $this->hintsUsed;
    }

    public function setHintsUsed(int $hintsUsed): static
    {
        $this->hintsUsed = $hintsUsed;

        return $this;
    }

    public function getDurationMs(): ?int
    {
        return $this->durationMs;
    }

    public function setDurationMs(int $durationMs): static
    {
        $this->durationMs = $durationMs;

        return $this;
    }

    public function isCompleted(): ?bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): static
    {
        $this->completed = $completed;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }
    public function getProgressSteps(): array
    {
        $values = array_map('intval', $this->progressSteps ?? []);
        $values = array_filter($values, static fn (int $step) => $step > 0);
        $values = array_values(array_unique($values));
        sort($values);

        return $values;
    }
    public function setProgressSteps(array $steps): static
    {
        $this->progressSteps = $this->normalizeSteps($steps);
        return $this;
    }
    public function addProgressStep(int $step): static
    {

        if ($step <= 0) {
            return $this;
        }

        $steps = $this->getProgressSteps();
        if (!\in_array($step, $steps, true)) {
            $steps[] = $step;
            sort($steps);
        }

        $this->progressSteps = $steps;

        return $this;
    }

    public function getEscapeGame(): ?EscapeGame
    {
        return $this->escapeGame;
    }

    public function getCurrentStep(): ?int
    {
        return $this->currentStep;
    }

    public function setCurrentStep(?int $currentStep): static
    {
        if ($currentStep !== null && $currentStep <= 0) {
            $currentStep = null;
        }

        $this->currentStep = $currentStep;

        return $this;
    }

    public function getProgressCount(): int
    {
        return \count($this->getProgressSteps());
    }

    public function setEscapeGame(?EscapeGame $escapeGame): static
    {
        $this->escapeGame = $escapeGame;

        return $this;
    }

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(?Participant $participant): static
    {
        $this->participant = $participant;

        return $this;
    }
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function touch(): static
    {
        $this->updatedAt = new DateTimeImmutable();

        return $this;
    }

    public function getResumeStep(int $totalSteps = 6): int
    {
        $totalSteps = max(1, $totalSteps);
        $progress = array_flip($this->getProgressSteps());

        for ($step = 1; $step <= $totalSteps; ++$step) {
            if (!isset($progress[$step])) {
                return $step;
            }
        }
        return $totalSteps;
    }

    private function computeScore(int $durationMs, int $hintsUsed, bool $completed): int {
        if (!$completed) return 0;
        $penaltyTime = intdiv(max(0, $durationMs), 30_000); // 1 pt / 30s
        return max(0, 100 - $hintsUsed - $penaltyTime);
    }

    /**
     * @param array<int|string, int|string> $steps
     */
    private function normalizeSteps(array $steps): array
    {
        $values = [];
        foreach ($steps as $value) {
            if (\is_int($value) || (\is_string($value) && ctype_digit($value))) {
                $int = (int) $value;
                if ($int > 0) {
                    $values[] = $int;
                }
            }
        }

        $values = array_values(array_unique($values));
        sort($values);

        return $values;
    }

}
