<?php

namespace App\Entity\Boards;

use App\Entity\Member\Activmember;
use App\Entity\Users\Contacts;
use App\Repository\TbsuggestRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;


#[ORM\Entity(repositoryClass: TbsuggestRepository::class)]
#[ORM\Table(name: 'aff_tbsuggest')]
class Tbsuggest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[OneToOne(targetEntity: Board::class)]
    #[JoinColumn(nullable: false)]
    private ?Board $preboard;

    #[OneToOne(targetEntity: Board::class)]
    #[JoinColumn(nullable: false)]
    private ?Board $invitor;

    #[OneToOne(targetEntity: Activmember::class)]
    #[JoinColumn(nullable: true)]
    private ?Activmember $member;

    #[OneToOne(targetEntity: Contacts::class)]
    #[JoinColumn(nullable: true)]
    private ?Contacts $contact;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $create_at;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $issuggest=false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $active=true;


    public function __construct()
    {
        $this->create_at=new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function isIssuggest(): ?bool
    {
        return $this->issuggest;
    }

    public function setIssuggest(bool $issuggest): self
    {
        $this->issuggest = $issuggest;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getPreboard(): ?Board
    {
        return $this->preboard;
    }

    public function setPreboard(Board $preboard): self
    {
        $this->preboard = $preboard;

        return $this;
    }

    public function getInvitor(): ?Board
    {
        return $this->invitor;
    }

    public function setInvitor(Board $invitor): self
    {
        $this->invitor = $invitor;

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

    public function getContact(): ?Contacts
    {
        return $this->contact;
    }

    public function setContact(?Contacts $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function issuggest(): ?bool
    {
        return $this->issuggest;
    }

    public function setSuggest(bool $issuggest): static
    {
        $this->issuggest = $issuggest;

        return $this;
    }



}