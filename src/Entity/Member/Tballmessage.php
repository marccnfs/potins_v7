<?php

namespace App\Entity\Member;

use App\Entity\LogMessages\MsgBoard;
use App\Entity\LogMessages\PrivateConvers;
use App\Entity\LogMessages\PublicationConvers;
use App\Entity\Users\Contacts;
use App\Repository\TballmessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TballmessageRepository::class)]
#[ORM\Table(name:"aff_allmessagesmember")]
class Tballmessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: PublicationConvers::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?PublicationConvers $tballmsgp;

    #[ORM\OneToOne(targetEntity: PrivateConvers::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?PrivateConvers $tballmsgd;

    #[ORM\OneToOne(targetEntity: MsgBoard::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?MsgBoard $tballmsgs;

    #[ORM\ManyToOne(targetEntity: Activmember::class, cascade: ['persist','remove'], inversedBy: 'allmessages')]
    private ?Activmember $member;

    #[ORM\ManyToOne(targetEntity: Contacts::class, cascade: ['persist','remove'], inversedBy: 'allmessages')]
    private ?Contacts $contact;

    #[ORM\Column(nullable: true)]
    private ?string $lastsender;

    #[ORM\Column(nullable: true)]
    private ?string $extract;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $lastconvers;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastsender(): ?string
    {
        return $this->lastsender;
    }

    public function setLastsender(?string $lastsender): self
    {
        $this->lastsender = $lastsender;

        return $this;
    }

    public function getExtract(): ?string
    {
        return $this->extract;
    }

    public function setExtract(?string $extract): self
    {
        $this->extract = $extract;

        return $this;
    }

    public function getLastconvers(): ?\DateTime
    {
        return $this->lastconvers;
    }

    public function setLastconvers(?\DateTime $lastconvers): self
    {
        $this->lastconvers = $lastconvers;

        return $this;
    }

    public function getTballmsgp(): ?PublicationConvers
    {
        return $this->tballmsgp;
    }

    public function setTballmsgp(?PublicationConvers $tballmsgp): self
    {
        $this->tballmsgp = $tballmsgp;

        return $this;
    }

    public function getTballmsgd(): ?PrivateConvers
    {
        return $this->tballmsgd;
    }

    public function setTballmsgd(?PrivateConvers $tballmsgd): self
    {
        $this->tballmsgd = $tballmsgd;

        return $this;
    }

    public function getTballmsgs(): ?MsgBoard
    {
        return $this->tballmsgs;
    }

    public function setTballmsgs(?MsgBoard $tballmsgs): self
    {
        $this->tballmsgs = $tballmsgs;

        return $this;
    }

    public function getMember(): ?activmember
    {
        return $this->member;
    }

    public function setMember(?activmember $member): self
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

}