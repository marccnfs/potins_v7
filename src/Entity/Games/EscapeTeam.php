<?php

namespace App\Entity\Games;

use App\Repository\EscapeTeamRepository;
use App\Entity\Games\EscapeTeamQrGroup;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EscapeTeamRepository::class)]
#[ORM\Table(name: 'aff_escape_team')]
#[ORM\UniqueConstraint(name: 'uniq_escape_team_run_name', columns: ['run_id', 'name'])]
class EscapeTeam
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: EscapeTeamRun::class, inversedBy: 'teams')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EscapeTeamRun $run = null;

    #[ORM\Column(length: 120)]
    private string $name = '';

    #[ORM\Column(length: 64)]
    private string $avatarKey = '';

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\OneToMany(targetEntity: EscapeTeamMember::class, mappedBy: 'team', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $members;

    #[ORM\OneToOne(targetEntity: EscapeTeamSession::class, mappedBy: 'team', cascade: ['persist', 'remove'])]
    private ?EscapeTeamSession $session = null;

    #[ORM\ManyToOne(targetEntity: EscapeTeamQrGroup::class)]
    private ?EscapeTeamQrGroup $qrGroup = null;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRun(): ?EscapeTeamRun
    {
        return $this->run;
    }

    public function setRun(?EscapeTeamRun $run): static
    {
        $this->run = $run;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

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

    /** @return Collection<int, EscapeTeamMember> */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(EscapeTeamMember $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->setTeam($this);
        }

        return $this;
    }

    public function removeMember(EscapeTeamMember $member): static
    {
        if ($this->members->removeElement($member)) {
            if ($member->getTeam() === $this) {
                $member->setTeam(null);
            }
        }

        return $this;
    }

    public function getSession(): ?EscapeTeamSession
    {
        return $this->session;
    }

    public function setSession(?EscapeTeamSession $session): static
    {
        $this->session = $session;
        if ($session !== null && $session->getTeam() !== $this) {
            $session->setTeam($this);
        }

        return $this;
    }


    public function getQrGroup(): ?EscapeTeamQrGroup
    {
        return $this->qrGroup;
    }

    public function setQrGroup(?EscapeTeamQrGroup $qrGroup): static
    {
        $this->qrGroup = $qrGroup;

        return $this;
    }
}
