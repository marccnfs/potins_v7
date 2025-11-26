<?php

namespace App\Entity\Games;

use App\Entity\Users\Participant;
use App\Repository\EscapeTeamRunRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EscapeTeamRunRepository::class)]
#[ORM\Table(name: 'aff_escape_team_run')]
#[ORM\UniqueConstraint(name: 'uniq_escape_team_run_slug', columns: ['share_slug'])]
class EscapeTeamRun
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_REGISTRATION = 'registration';
    public const STATUS_LOCKED = 'locked';
    public const STATUS_RUNNING = 'running';
    public const STATUS_ENDED = 'ended';

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: EscapeGame::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?EscapeGame $escapeGame = null;

    #[ORM\ManyToOne(targetEntity: Participant::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Participant $owner = null;

    #[ORM\Column(length: 180)]
    private string $title = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heroImageUrl = null;

    #[ORM\Column(length: 120)]
    private string $shareSlug = '';

    #[ORM\Column(length: 24)]
    private string $status = 'draft';

    #[ORM\Column(type: 'integer', options: ['default' => 10])]
    private int $maxTeams = 10;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $timeLimitSeconds = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $registrationOpenedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $endedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $puzzleConfig = [];

    #[ORM\OneToMany(targetEntity: EscapeTeam::class, mappedBy: 'run', cascade: ['remove'], orphanRemoval: true)]
    private Collection $teams;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEscapeGame(): ?EscapeGame
    {
        return $this->escapeGame;
    }

    public function setEscapeGame(EscapeGame $escapeGame): static
    {
        $this->escapeGame = $escapeGame;

        return $this;
    }

    public function getOwner(): ?Participant
    {
        return $this->owner;
    }

    public function setOwner(?Participant $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title ?? $this->escapeGame?->getTitle();
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getHeroImageUrl(): ?string
    {
        return $this->heroImageUrl;
    }

    public function setHeroImageUrl(?string $heroImageUrl): static
    {
        $this->heroImageUrl = $heroImageUrl;

        return $this;
    }

    public function getShareSlug(): ?string
    {
        return $this->shareSlug;
    }

    public function setShareSlug(string $shareSlug): static
    {
        $this->shareSlug = $shareSlug;

        return $this;
    }

    /** Génère un slug de partage si absent (ex: page d'accueil projetée). */
    public function ensureShareSlug(callable $slugger): void
    {
        if ($this->shareSlug !== '') {
            return;
        }

        $seed = ($this->getTitle() ?: 'escape-team') . '-' . bin2hex(random_bytes(4));
        $this->shareSlug = $slugger($seed);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isRegistrationOpen(): bool
    {
        return $this->status === self::STATUS_REGISTRATION && $this->startedAt === null;
    }

    public function getMaxTeams(): int
    {
        return $this->maxTeams;
    }

    public function setMaxTeams(int $maxTeams): static
    {
        $this->maxTeams = max(1, $maxTeams);

        return $this;
    }

    public function getTimeLimitSeconds(): ?int
    {
        return $this->timeLimitSeconds;
    }

    public function setTimeLimitSeconds(?int $timeLimitSeconds): static
    {
        $this->timeLimitSeconds = $timeLimitSeconds !== null ? max(0, $timeLimitSeconds) : null;

        return $this;
    }

    public function getRegistrationOpenedAt(): ?DateTimeImmutable
    {
        return $this->registrationOpenedAt;
    }

    public function setRegistrationOpenedAt(?DateTimeImmutable $registrationOpenedAt): static
    {
        $this->registrationOpenedAt = $registrationOpenedAt;

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

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    public function getPuzzleConfig(): array
    {
        return $this->puzzleConfig ?? [];
    }

    public function setPuzzleConfig(?array $puzzleConfig): static
    {
        $this->puzzleConfig = $puzzleConfig ?? [];

        return $this;
    }


    /** @return Collection<int, EscapeTeam> */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(EscapeTeam $team): static
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
            $team->setRun($this);
        }

        return $this;
    }

    public function removeTeam(EscapeTeam $team): static
    {
        if ($this->teams->removeElement($team)) {
            if ($team->getRun() === $this) {
                $team->setRun(null);
            }
        }

        return $this;
    }
}
