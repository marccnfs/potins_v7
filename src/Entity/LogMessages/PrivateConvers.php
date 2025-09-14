<?php


namespace App\Entity\LogMessages;

use App\Entity\Member\Activmember;
use App\Entity\Member\Tballmessage;
use App\Entity\Boards\Board;
use App\Repository\PrivateConversRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;



#[ORM\Entity(repositoryClass: PrivateConversRepository::class)]
#[ORM\Table(name:"aff_privateconvers")]
class PrivateConvers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Activmember::class)]
    private Collection $memberdest;

    #[ORM\ManyToOne(targetEntity: Activmember::class)]
    private ?Activmember $memberopen= null;

    #[ORM\OneToOne(targetEntity: Tballmessage::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Tballmessage $tballmsgd;

    #[ORM\Column(nullable: false)]
    private ?string $subject;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $dateclosed;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $sender = true;

    #[ORM\OneToMany(mappedBy: 'conversprivate', targetEntity: MsgsD::class, cascade: ['persist', 'remove'])]
    private Collection $msgs;

    public function __construct()
    {
        $this->create_at=new DateTime();
        $this->memberdest = new ArrayCollection();
        $this->msgs = new ArrayCollection();
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

    public function getSender(): ?bool
    {
        return $this->sender;
    }

    public function setSender(?bool $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function isSender(): ?bool
    {
        return $this->sender;
    }

    /**
     * @return Collection<int, Activmember>
     */
    public function getMemberdest(): Collection
    {
        return $this->memberdest;
    }

    public function addMemberdest(Activmember $memberdest): self
    {
        if (!$this->memberdest->contains($memberdest)) {
            $this->memberdest->add($memberdest);
        }

        return $this;
    }

    public function removeMemberdest(Activmember $memberdest): self
    {
        $this->memberdest->removeElement($memberdest);

        return $this;
    }

    public function getMemberopen(): ?Activmember
    {
        return $this->memberopen;
    }

    public function setMemberopen(?Activmember $memberopen): self
    {
        $this->memberopen = $memberopen;

        return $this;
    }

    public function getTballmsgd(): ?Tballmessage
    {
        return $this->tballmsgd;
    }

    public function setTballmsgd(?Tballmessage $tballmsgd): self
    {
        $this->tballmsgd = $tballmsgd;

        return $this;
    }

    /**
     * @return Collection<int, MsgsD>
     */
    public function getMsgs(): Collection
    {
        return $this->msgs;
    }

    public function addMsg(MsgsD $msg): self
    {
        if (!$this->msgs->contains($msg)) {
            $this->msgs->add($msg);
            $msg->setConversprivate($this);
        }

        return $this;
    }

    public function removeMsg(MsgsD $msg): self
    {
        if ($this->msgs->removeElement($msg)) {
            // set the owning side to null (unless already changed)
            if ($msg->getConversprivate() === $this) {
                $msg->setConversprivate(null);
            }
        }

        return $this;
    }
}