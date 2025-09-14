<?php


namespace App\Entity\HyperCom;

use App\Entity\Member\Activmember;
use App\Repository\TagAnalyticRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: TagAnalyticRepository::class)]
#[ORM\Table(name:"aff_taganalytic")]
class TagAnalytic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Activmember::class, mappedBy: 'analityc')]
    private ?Activmember $member=null;

    #[ORM\Column(type: Types::JSON, nullable: true )]
    private ?array $tabgps = [];

    #[ORM\Column(type: Types::JSON, nullable: true )]
    private ?array $tabcat = [];

    #[ORM\Column(type: Types::JSON, nullable: true )]
    private ?array $tablikeboard = [];

    #[ORM\Column(type: Types::JSON, nullable: true )]
    private ?array $sessions = [];

    #[ORM\Column(nullable: true)]
    private ?string $log = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTabgps(): ?array
    {
        return $this->tabgps;
    }

    public function setTabgps(?array $tabgps): self
    {
        $this->tabgps = $tabgps;

        return $this;
    }

    public function getTabcat(): ?array
    {
        return $this->tabcat;
    }

    public function setTabcat(?array $tabcat): self
    {
        $this->tabcat = $tabcat;

        return $this;
    }

    public function getSessions(): ?array
    {
        return $this->sessions;
    }

    public function setSessions(?array $sessions): self
    {
        $this->sessions = $sessions;

        return $this;
    }

    public function getLog(): ?string
    {
        return $this->log;
    }

    public function setLog(?string $log): self
    {
        $this->log = $log;

        return $this;
    }

    public function getTablikeboard(): array
    {
        return $this->tablikeboard;
    }

    public function setTablikeboard(?array $tablikeboard): self
    {
        $this->tablikeboard = $tablikeboard;

        return $this;
    }

    public function getMember(): ?Activmember
    {
        return $this->member;
    }

    public function setMember(?Activmember $member): self
    {
        $this->member = $member;

        return $this;
    }

}
