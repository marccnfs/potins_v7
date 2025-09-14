<?php

namespace App\Entity\Notifications;


use App\Entity\member\Activmember;
use App\Repository\NotifmemberRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: NotifmemberRepository::class)]
#[ORM\Table(name:"aff_notifmember")]
class Notifmember
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Activmember::class,inversedBy: 'tbnotifs')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Activmember $member= null;

    #[ORM\Column(nullable: true)]
    private ?string $subject = null;

    #[ORM\Column(length: 50,nullable: false)]
    private ?string $classmodule;

    #[ORM\Column(type: Types::INTEGER,nullable: true)]
    private ?int $idmodule = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    public function __construct()
    {
        $this->create_at=new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getClassmodule(): ?string
    {
        return $this->classmodule;
    }

    public function setClassmodule(string $classmodule): self
    {
        $this->classmodule = $classmodule;

        return $this;
    }

    public function getCreateAt(): ?\DateTime
    {
        return $this->create_at;
    }

    public function setCreateAt(\DateTime $create_at): self
    {
        $this->create_at = $create_at;

        return $this;
    }

    public function getIdmodule(): ?int
    {
        return $this->idmodule;
    }

    public function setIdmodule(?int $idmodule): self
    {
        $this->idmodule = $idmodule;

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