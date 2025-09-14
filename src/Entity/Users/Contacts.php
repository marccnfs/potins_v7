<?php


namespace App\Entity\Users;


use App\Entity\Member\Tballmessage;
use App\Repository\ContactRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ORM\Table(name:"aff_contact")]
class Contacts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $ipcontact = null;

    #[ORM\Column(type: Types::JSON, nullable: true )]
    private ?array $loginsource = [];

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $validatetop = false;

    #[ORM\Column(unique: true)]
    private ?string $emailCanonical = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $datemaj_at;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active = true;

    #[ORM\OneToOne(targetEntity: ProfilUser::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?ProfilUser $useridentity;

    #[ORM\OneToMany(mappedBy: 'contact', targetEntity: Tballmessage::class)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $allmessages;

    #[ORM\OneToMany(mappedBy: 'contact', targetEntity: Commentrdv::class, orphanRemoval: true)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $comments;

    public function __construct()
    {
        $this->create_at=new DateTime();
        $this->allmessages = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }
    public function __toString(): string
    {
        return $this->emailCanonical;
    }
    public function getId(): ?int
    {
        return $this->id;
    }


    public function getEmailCanonical(): ?string
    {
        return $this->emailCanonical;
    }

    public function setEmailCanonical(string $emailCanonical): self
    {
        $this->emailCanonical = $emailCanonical;

        return $this;
    }

    public function getCreateAt(): ?\DateTime
    {
        return $this->create_at;
    }

    public function getDatemajAt(): ?\DateTime
    {
        return $this->datemaj_at;
    }

    public function setDatemajAt(?\DateTime $datemaj_at): self
    {
        $this->datemaj_at = $datemaj_at;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getUseridentity(): ?ProfilUser
    {
        return $this->useridentity;
    }

    public function setUseridentity(ProfilUser $useridentity): self
    {
        $this->useridentity = $useridentity;

        return $this;
    }

    public function getIpcontact(): ?string
    {
        return $this->ipcontact;
    }

    public function setIpcontact(?string $ipcontact): self
    {
        $this->ipcontact = $ipcontact;

        return $this;
    }

    public function getLoginsource(): ?array
    {
        return $this->loginsource;
    }

    public function setLoginsource(?array $loginsource): self
    {
        $this->loginsource = $loginsource;

        return $this;
    }

    public function addLoginsource($loginsource): static
    {
        $loginsource = strtoupper($loginsource);

        if (!in_array($loginsource, $this->loginsource, true)) {
            $this->loginsource[] = $loginsource;
        }

        return $this;
    }

    public function getValidatetop(): ?bool
    {
        return $this->validatetop;
    }

    public function setValidatetop(bool $validatetop): self
    {
        $this->validatetop = $validatetop;

        return $this;
    }

    public function setCreateAt(\DateTime $create_at): self
    {
        $this->create_at = $create_at;

        return $this;
    }

    public function isValidatetop(): ?bool
    {
        return $this->validatetop;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    /**
     * @return Collection<int, Tballmessage>
     */
    public function getAllmessages(): Collection
    {
        return $this->allmessages;
    }

    public function addAllmessage(Tballmessage $allmessage): static
    {
        if (!$this->allmessages->contains($allmessage)) {
            $this->allmessages->add($allmessage);
            $allmessage->setContact($this);
        }

        return $this;
    }

    public function removeAllmessage(Tballmessage $allmessage): static
    {
        if ($this->allmessages->removeElement($allmessage)) {
            // set the owning side to null (unless already changed)
            if ($allmessage->getContact() === $this) {
                $allmessage->setContact(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentrdv>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Commentrdv $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setContact($this);
        }

        return $this;
    }

    public function removeComment(Commentrdv $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getContact() === $this) {
                $comment->setContact(null);
            }
        }

        return $this;
    }
}