<?php

namespace App\Entity\Games;

use App\Repository\EscapeTeamQrPageRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EscapeTeamQrPageRepository::class)]
#[ORM\Table(name: 'aff_escape_team_qr_page')]
#[ORM\Index(columns: ['token'])]
class EscapeTeamQrPage
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: EscapeTeamQrGroup::class, inversedBy: 'pages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EscapeTeamQrGroup $group = null;

    #[ORM\Column(length: 120)]
    private string $teamName = '';

    #[ORM\Column(length: 8)]
    private string $identificationCode = '';

    #[ORM\Column(length: 255)]
    private string $message = '';

    #[ORM\Column(length: 64, unique: true)]
    private string $token = '';

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->token = bin2hex(random_bytes(12));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroup(): ?EscapeTeamQrGroup
    {
        return $this->group;
    }

    public function setGroup(?EscapeTeamQrGroup $group): static
    {
        $this->group = $group;

        return $this;
    }

    public function getTeamName(): string
    {
        return $this->teamName;
    }

    public function setTeamName(string $teamName): static
    {
        $this->teamName = trim($teamName);

        return $this;
    }

    public function getIdentificationCode(): string
    {
        return $this->identificationCode;
    }

    public function setIdentificationCode(string $identificationCode): static
    {
        $this->identificationCode = trim($identificationCode);

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

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
}
