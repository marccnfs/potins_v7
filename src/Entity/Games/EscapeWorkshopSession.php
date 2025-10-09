<?php

declare(strict_types=1);

namespace App\Entity\Games;

use App\Entity\Module\PostEvent;
use App\Repository\EscapeWorkshopSessionRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EscapeWorkshopSessionRepository::class)]
#[ORM\Table(name: 'escape_workshop_session')]
#[ORM\UniqueConstraint(name: 'uniq_escape_workshop_code', columns: ['code'])]
class EscapeWorkshopSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $label;

    #[ORM\Column(length: 16)]
    private string $code;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isMaster = false;

    #[ORM\ManyToOne(targetEntity: PostEvent::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?PostEvent $event = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, EscapeGame>
     */
    #[ORM\OneToMany(targetEntity: EscapeGame::class, mappedBy: 'workshopSession')]
    private Collection $escapeGames;

    public function __construct(string $label = '')
    {
        $this->label = $label !== '' ? $label : 'Session escape game';
        $now = new DateTimeImmutable('now');
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->escapeGames = new ArrayCollection();
        $this->code = '0000';
    }

    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = trim($label) !== '' ? trim($label) : 'Session escape game';
        $this->touch();

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $code = strtoupper(trim($code));
        $this->code = $code;
        $this->touch();

        return $this;
    }

    public function isMaster(): bool
    {
        return $this->isMaster;
    }

    public function setIsMaster(bool $isMaster): self
    {
        $this->isMaster = $isMaster;
        if ($isMaster) {
            $this->event = null;
        }
        $this->touch();

        return $this;
    }

    public function getEvent(): ?PostEvent
    {
        return $this->event;
    }

    public function setEvent(?PostEvent $event): self
    {
        if ($this->isMaster) {
            $this->event = null;
        } else {
            $this->event = $event;
        }
        $this->touch();

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        $this->touch();

        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return Collection<int, EscapeGame>
     */
    public function getEscapeGames(): Collection
    {
        return $this->escapeGames;
    }

    public function addEscapeGame(EscapeGame $escapeGame): self
    {
        if (!$this->escapeGames->contains($escapeGame)) {
            $this->escapeGames->add($escapeGame);
            $escapeGame->setWorkshopSession($this);
        }

        return $this;
    }

    public function removeEscapeGame(EscapeGame $escapeGame): self
    {
        if ($this->escapeGames->removeElement($escapeGame)) {
            if ($escapeGame->getWorkshopSession() === $this) {
                $escapeGame->setWorkshopSession(null);
            }
        }

        return $this;
    }

    public function getDisplayName(): string
    {
        if ($this->isMaster()) {
            return $this->label ?: 'Code maître escape game';
        }

        if ($this->label) {
            return $this->label;
        }

        return $this->event?->getTitre() ?? 'Session escape game';
    }
}
