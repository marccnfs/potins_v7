<?php

namespace App\Entity\Agenda;

use App\Entity\Users\Participant;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\Types\UuidType;

#[ORM\Entity]
#[ORM\Table(name: 'agenda_events')]
#[ORM\Index(columns: ['starts_at'])]
#[ORM\Index(columns: ['published', 'visibility'])]
#[ORM\Index(columns: ['commune_code'])]
class Event
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 160, unique: true)]
    private string $slug;

    #[ORM\Column(length: 140)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Participant::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Participant $organizer;

    #[ORM\Column(length: 24, options: ['default' => 'autre'])]
    private string $communeCode = 'autre';

    #[ORM\Column(name: 'starts_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $startsAt; // UTC

    #[ORM\Column(name: 'ends_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $endsAt; // UTC

    #[ORM\Column(length: 64)]
    private string $timezone = 'Europe/Paris';

    #[ORM\Column(type: 'boolean')]
    private bool $isAllDay = false;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $locationName = null;

    #[ORM\Column(length: 250, nullable: true)]
    private ?string $locationAddress = null;

    #[ORM\Column(nullable: true)]
    private ?int $capacity = null; // null = illimité

    #[ORM\Column(length: 16)]
    private string $visibility = 'public'; // public|unlisted|private

    #[ORM\Column(length: 16)]
    private string $status = 'scheduled';  // scheduled|cancelled|completed|draft

    #[ORM\Column(type: 'boolean')]
    private bool $published = false;

    #[ORM\Column(length: 48)]
    private string $category = 'atelier'; // atelier|rdv|externe|autre

    // Lien optionnel vers une autre ressource (ex: EscapeGame)
    #[ORM\Column(length: 48, nullable: true)]
    private ?string $sourceType = null; // ex: 'escape_game'

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $sourceId = null; // id ou uuid de la ressource

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        Participant        $organizer,
        string             $title,
        \DateTimeImmutable $startsAtUtc,
        \DateTimeImmutable $endsAtUtc,
        string             $timezone = 'Europe/Paris'
    )
    {
        $this->id = Uuid::v7();
        $this->organizer = $organizer;
        $this->title = $title;
        $this->startsAt = $startsAtUtc;
        $this->endsAt = $endsAtUtc;
        $this->timezone = $timezone;
        $this->slug = self::slugify($title . '-' . bin2hex(random_bytes(3)));
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public static function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return $text ?: 'event';
    }

    public function getCommuneCode(): string { return $this->communeCode; }
    public function setCommuneCode(string $code): void { $this->communeCode = $code; $this->touch(); }

    // --- Getters/Setters (subset) ---
    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $t): void
    {
        $this->title = $t;
        $this->touch();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $d): void
    {
        $this->description = $d;
        $this->touch();
    }

    public function getOrganizer(): Participant
    {
        return $this->organizer;
    }

    public function setOrganizer(Participant $p): void
    {
        $this->organizer = $p;
        $this->touch();
    }

    public function getStartsAt(): \DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function getEndsAt(): \DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setPeriod(\DateTimeImmutable $startUtc, \DateTimeImmutable $endUtc): void
    {
        $this->startsAt = $startUtc;
        $this->endsAt = $endUtc;
        $this->touch();
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function setTimezone(string $tz): void
    {
        $this->timezone = $tz;
        $this->touch();
    }

    public function isAllDay(): bool
    {
        return $this->isAllDay;
    }

    public function setAllDay(bool $allDay): void
    {
        $this->isAllDay = $allDay;
        $this->touch();
    }

    public function getLocationName(): ?string
    {
        return $this->locationName;
    }

    public function setLocationName(?string $n): void
    {
        $this->locationName = $n;
        $this->touch();
    }

    public function getLocationAddress(): ?string
    {
        return $this->locationAddress;
    }

    public function setLocationAddress(?string $a): void
    {
        $this->locationAddress = $a;
        $this->touch();
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(?int $c): void
    {
        $this->capacity = $c;
        $this->touch();
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $v): void
    {
        $this->visibility = $v;
        $this->touch();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $s): void
    {
        $this->status = $s;
        $this->touch();
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $p): void
    {
        $this->published = $p;
        $this->touch();
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $c): void
    {
        $this->category = $c;
        $this->touch();
    }

    public function getSourceType(): ?string
    {
        return $this->sourceType;
    }

    public function setSourceType(?string $t): void
    {
        $this->sourceType = $t;
        $this->touch();
    }

    public function getSourceId(): ?string
    {
        return $this->sourceId;
    }

    public function setSourceId(?string $id): void
    {
        $this->sourceId = $id;
        $this->touch();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setStartsAt(\DateTimeImmutable $startsAt): static
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function setEndsAt(\DateTimeImmutable $endsAt): static
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function setIsAllDay(bool $isAllDay): static
    {
        $this->isAllDay = $isAllDay;

        return $this;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}

