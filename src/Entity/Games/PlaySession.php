<?php

namespace App\Entity\Games;

use App\Entity\Users\Participant;
use App\Repository\PlaySessionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaySessionRepository::class)]
#[ORM\Table(name:"aff_playsession")]
#[ORM\Index(columns: ['created_at'])]
#[ORM\Index(columns: ['score'])]
class PlaySession
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: EscapeGame::class,inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EscapeGame $escapeGame = null;

    #[ORM\ManyToOne(targetEntity: Participant::class,inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $participant = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(type: 'integer')]
    private int $hintsUsed = 0;

    #[ORM\Column(type: 'integer')]
    private int $durationMs = 0; // total

    #[ORM\Column(type: 'boolean')]
    private bool $completed = false;

    #[ORM\Column(type: 'integer')]
    private int $score = 0;

    public function __construct() {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): static
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

    public function getEscapeGame(): ?EscapeGame
    {
        return $this->escapeGame;
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

    private function computeScore(int $durationMs, int $hintsUsed, bool $completed): int {
        if (!$completed) return 0;
        $penaltyTime = intdiv(max(0, $durationMs), 30_000); // 1 pt / 30s
        return max(0, 100 - $hintsUsed - $penaltyTime);
    }

}
