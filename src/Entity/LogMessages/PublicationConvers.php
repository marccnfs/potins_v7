<?php


namespace App\Entity\LogMessages;


use App\Entity\Member\Activmember;
use App\Entity\Member\Tballmessage;
use App\Entity\Module\TabpublicationMsgs;
use App\Entity\Users\Contacts;
use App\Repository\PublicationConversRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: PublicationConversRepository::class)]
#[ORM\Table(name:"aff_publicationconvers")]
class PublicationConvers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $ismembersender = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isclient = false;

    #[ORM\OneToOne(targetEntity: Tballmessage::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Tballmessage $tballmsgp;

    #[ORM\Column(nullable: true)]
    private ?string $subject;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $dateclosed;

    #[ORM\ManyToOne(targetEntity: TabpublicationMsgs::class, inversedBy: 'idmessage')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TabpublicationMsgs $tabpublication;

    #[ORM\OneToMany(mappedBy: 'publicationmsg', targetEntity: MsgsP::class, cascade: ['persist', 'remove'])]
    private Collection $msgs;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $sender = true;

    public function __construct()
    {
        $this->create_at=new DateTime();
        $this->msgs = new ArrayCollection();
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

    public function getDateclosed(): ?\DateTime
    {
        return $this->dateclosed;
    }

    public function setDateclosed(?\DateTime $dateclosed): self
    {
        $this->dateclosed = $dateclosed;

        return $this;
    }

    public function getTballmsgp(): ?Tballmessage
    {
        return $this->tballmsgp;
    }

    public function setTballmsgp(?Tballmessage $tballmsgp): self
    {
        $this->tballmsgp = $tballmsgp;

        return $this;
    }

    public function getTabpublication(): ?TabpublicationMsgs
    {
        return $this->tabpublication;
    }

    public function setTabpublication(?TabpublicationMsgs $tabpublication): self
    {
        $this->tabpublication = $tabpublication;

        return $this;
    }

    public function isIsmembersender(): ?bool
    {
        return $this->ismembersender;
    }

    public function setIsmembersender(bool $ismembersender): self
    {
        $this->ismembersender = $ismembersender;

        return $this;
    }

    public function isIsclient(): ?bool
    {
        return $this->isclient;
    }

    public function setIsclient(bool $isclient): self
    {
        $this->isclient = $isclient;

        return $this;
    }

    public function isSender(): ?bool
    {
        return $this->sender;
    }

    public function setSender(?bool $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return Collection<int, MsgsP>
     */
    public function getMsgs(): Collection
    {
        return $this->msgs;
    }

    public function addMsg(MsgsP $msg): self
    {
        if (!$this->msgs->contains($msg)) {
            $this->msgs->add($msg);
            $msg->setPublicationmsg($this);
        }

        return $this;
    }

    public function removeMsg(MsgsP $msg): self
    {
        if ($this->msgs->removeElement($msg)) {
            // set the owning side to null (unless already changed)
            if ($msg->getPublicationmsg() === $this) {
                $msg->setPublicationmsg(null);
            }
        }

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

    public function ismembersender(): ?bool
    {
        return $this->ismembersender;
    }

    public function setMembersender(bool $ismembersender): static
    {
        $this->ismembersender = $ismembersender;

        return $this;
    }

    public function isclient(): ?bool
    {
        return $this->isclient;
    }

    public function setClient(bool $isclient): static
    {
        $this->isclient = $isclient;

        return $this;
    }
}