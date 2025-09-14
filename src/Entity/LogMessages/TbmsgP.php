<?php


namespace App\Entity\LogMessages;


use App\Entity\Notifications\Notifmember;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name:"aff_tabreadpublication")]
class TbmsgP
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MsgsP::class, inversedBy: 'tabreaders')]
    #[ORM\JoinColumn(nullable: true)]
    private ?MsgsP $idmessage= null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $idmember = null;

    #[ORM\OneToOne(targetEntity: Notifmember::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Notifmember $tabnotifs;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isRead = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $read_at;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $removed = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getReadAt(): ?\DateTime
    {
        return $this->read_at;
    }

    public function setReadAt(?\DateTime $read_at): self
    {
        $this->read_at = $read_at;

        return $this;
    }

    public function getRemoved(): ?bool
    {
        return $this->removed;
    }

    public function setRemoved(bool $removed): self
    {
        $this->removed = $removed;

        return $this;
    }

    public function getIdmessage(): ?MsgsP
    {
        return $this->idmessage;
    }

    public function setIdmessage(?MsgsP $idmessage): self
    {
        $this->idmessage = $idmessage;

        return $this;
    }

    public function getIdmember(): ?int
    {
        return $this->idmember;
    }

    public function setIdmember(?int $idmember): self
    {
        $this->idmember = $idmember;

        return $this;
    }

    public function isIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function isRemoved(): ?bool
    {
        return $this->removed;
    }

    public function getTabnotifs(): ?Notifmember
    {
        return $this->tabnotifs;
    }

    public function setTabnotifs(?Notifmember $tabnotifs): self
    {
        $this->tabnotifs = $tabnotifs;

        return $this;
    }

    public function isRead(): ?bool
    {
        return $this->isRead;
    }

    public function setRead(bool $isRead): static
    {
        $this->isRead = $isRead;

        return $this;
    }
}