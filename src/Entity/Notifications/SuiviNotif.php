<?php

namespace App\Entity\Notifications;


use App\Entity\Boards\board;
use App\Entity\Member\Activmember;
use App\Entity\Users\Contacts;
use App\Repository\SuiviNotifRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: SuiviNotifRepository::class)]
#[ORM\Table(name:"aff_suivinotif")]
class SuiviNotif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isread = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $ismember = false;

    #[ORM\ManyToOne(targetEntity: Activmember::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Activmember $member= null;

    #[ORM\ManyToOne(targetEntity: Board::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Board $board= null;

    #[ORM\ManyToOne(targetEntity: Contacts::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Contacts $contact= null;

    #[ORM\Column(nullable: true)]
    private ?string $subject = null;

    #[ORM\Column(nullable: true)]
    private ?string $content = null;

    #[ORM\Column(nullable: true)]
    private ?string $mediasource = null;

    #[ORM\Column(length: 50,nullable: true)]
    private ?string $classmodule = null;

    #[ORM\Column(type: Types::INTEGER,nullable: true)]
    private ?int $classmoduleid = null;

    #[ORM\Column(nullable: true)]
    private ?string $keymodule = null;

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

    public function getIsread(): ?bool
    {
        return $this->isread;
    }

    public function setIsread(bool $isread): self
    {
        $this->isread = $isread;

        return $this;
    }

    public function getIsmember(): ?bool
    {
        return $this->ismember;
    }

    public function setIsmember(bool $ismember): self
    {
        $this->ismember = $ismember;

        return $this;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getMediasource(): ?string
    {
        return $this->mediasource;
    }

    public function setMediasource(?string $mediasource): self
    {
        $this->mediasource = $mediasource;

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

    public function getClassmoduleid(): ?int
    {
        return $this->classmoduleid;
    }

    public function setClassmoduleid(?int $classmoduleid): self
    {
        $this->classmoduleid = $classmoduleid;

        return $this;
    }

    public function getKeymodule(): ?string
    {
        return $this->keymodule;
    }

    public function setKeymodule(?string $keymodule): self
    {
        $this->keymodule = $keymodule;

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

    public function isIsread(): ?bool
    {
        return $this->isread;
    }

    public function isIsmember(): ?bool
    {
        return $this->ismember;
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

    public function getBoard(): ?board
    {
        return $this->board;
    }

    public function setBoard(?board $board): self
    {
        $this->board = $board;

        return $this;
    }

    public function getContact(): ?Contacts
    {
        return $this->contact;
    }

    public function setContact(?Contacts $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function isread(): ?bool
    {
        return $this->isread;
    }

    public function setRead(bool $isread): static
    {
        $this->isread = $isread;

        return $this;
    }

    public function ismember(): ?bool
    {
        return $this->ismember;
    }

}