<?php


namespace App\Entity\LogMessages;


use App\Entity\Member\Activmember;
use App\Entity\Member\Tballmessage;
use App\Entity\Users\Contacts;
use App\Entity\Boards\Board;
use App\Repository\MsgWebisteRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: MsgWebisteRepository::class)]
#[ORM\Table(name:"aff_msgwebsite")]
class MsgBoard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $ismember = false;

    #[ORM\OneToOne(targetEntity: Tballmessage::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Tballmessage $tballmsgs;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isclient = false;

    #[ORM\ManyToOne(targetEntity: Board::class, cascade: ['persist', 'remove'], inversedBy: 'msgs')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Board $boarddest= null;

    #[ORM\ManyToOne(targetEntity: Activmember::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Activmember $memberexpe= null;

    #[ORM\ManyToOne(targetEntity: Contacts::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Contacts $contactexp= null;

    #[ORM\Column(nullable: true)]
    private ?string $subject = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $dateClosed;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $sender = true;

    #[ORM\OneToMany(mappedBy: 'msgboard', targetEntity: Msgs::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $msgs;

    public function __construct()
    {
        $this->create_at=new DateTime();
        $this->msgs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsclient(): ?bool
    {
        return $this->isclient;
    }

    public function setIsclient(bool $isclient): self
    {
        $this->isclient = $isclient;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

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

    public function getDateClosed(): ?\DateTime
    {
        return $this->dateClosed;
    }

    public function setDateClosed(?\DateTime $dateClosed): self
    {
        $this->dateClosed = $dateClosed;

        return $this;
    }

    public function getSender(): ?bool
    {
        return $this->sender;
    }

    public function setSender(?bool $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getTballmsgs(): ?Tballmessage
    {
        return $this->tballmsgs;
    }

    public function setTballmsgs(?Tballmessage $tballmsgs): self
    {
        $this->tballmsgs = $tballmsgs;

        return $this;
    }

    public function getContactexp(): ?Contacts
    {
        return $this->contactexp;
    }

    public function setContactexp(?Contacts $contactexp): self
    {
        $this->contactexp = $contactexp;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getMsgs(): Collection
    {
        return $this->msgs;
    }

    public function isIsmember(): ?bool
    {
        return $this->ismember;
    }

    public function setIsmember(bool $ismember): self
    {
        $this->ismember = $ismember;

        return $this;
    }

    public function isIsclient(): ?bool
    {
        return $this->isclient;
    }

    public function isSender(): ?bool
    {
        return $this->sender;
    }

    public function getBoarddest(): ?Board
    {
        return $this->boarddest;
    }

    public function setBoarddest(?Board $boarddest): self
    {
        $this->boarddest = $boarddest;

        return $this;
    }

    public function getMemberexpe(): ?Activmember
    {
        return $this->memberexpe;
    }

    public function setMemberexpe(?Activmember $memberexpe): self
    {
        $this->memberexpe = $memberexpe;

        return $this;
    }

    public function addMsg(Msgs $msg): self
    {
        if (!$this->msgs->contains($msg)) {
            $this->msgs->add($msg);
            $msg->setMsgboard($this);
        }

        return $this;
    }

    public function removeMsg(Msgs $msg): self
    {
        if ($this->msgs->removeElement($msg)) {
            // set the owning side to null (unless already changed)
            if ($msg->getMsgboard() === $this) {
                $msg->setMsgboard(null);
            }
        }

        return $this;
    }

    public function ismember(): ?bool
    {
        return $this->ismember;
    }

    public function setMember(bool $ismember): static
    {
        $this->ismember = $ismember;

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