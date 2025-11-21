<?php

namespace App\Entity\Games;

use App\Repository\EscapeTeamMemberRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EscapeTeamMemberRepository::class)]
#[ORM\Table(name: 'aff_escape_team_member')]
class EscapeTeamMember
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: EscapeTeam::class, inversedBy: 'members')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EscapeTeam $team = null;

    #[ORM\Column(length: 80)]
    private string $nickname = '';

    #[ORM\Column(length: 64)]
    private string $avatarKey = '';

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeam(): ?EscapeTeam
    {
        return $this->team;
    }

    public function setTeam(?EscapeTeam $team): static
    {
        $this->team = $team;

        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): static
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getAvatarKey(): ?string
    {
        return $this->avatarKey;
    }

    public function setAvatarKey(string $avatarKey): static
    {
        $this->avatarKey = $avatarKey;

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
