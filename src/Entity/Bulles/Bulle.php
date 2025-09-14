<?php


namespace App\Entity\Bulles;

use App\Entity\Customer\Customers;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name:'aff_bulles')]
#[ORM\Index(columns:['modulebubble'])]
class Bulle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $deleted=false;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $quality = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $nbrvieuw =0;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $nbreponse;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $lasttrip_at;

    #[ORM\Column(nullable: true)]
    private ?string $spacevisiting=null;

    #[ORM\ManyToMany(targetEntity: Customers::class, mappedBy: 'bulles')]
    private ?Collection $customer;

    #[ORM\Column(nullable: false)]
    private ?string $modulebubble;

    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private ?int $idmodule = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $expire_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $dateUpdate;

    #[ORM\OneToMany(targetEntity: Bullette::class, mappedBy: "bulle")]
    #[ORM\JoinColumn(nullable:false)]
    private Collection $bullettes;

     public function __construct()
    {
        $this->create_at=new DateTime();
        $this->lasttrip_at=new DateTime();
        $this->bullettes = new ArrayCollection();
        $this->nbrvieuw =1;
        $this->customer = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getQuality(): ?int
    {
        return $this->quality;
    }

    public function setQuality(?int $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    public function getNbrvieuw(): ?int
    {
        return $this->nbrvieuw;
    }

    public function setNbrvieuw(?int $nbrvieuw): self
    {
        $this->nbrvieuw = $nbrvieuw;

        return $this;
    }

    public function getNbreponse(): ?int
    {
        return $this->nbreponse;
    }

    public function setNbreponse(?int $nbreponse): self
    {
        $this->nbreponse = $nbreponse;

        return $this;
    }

    public function getLasttripAt(): ?\DateTime
    {
        return $this->lasttrip_at;
    }

    public function setLasttripAt(\DateTime $lasttrip_at): self
    {
        $this->lasttrip_at = $lasttrip_at;

        return $this;
    }

    public function getSpacevisiting(): ?string
    {
        return $this->spacevisiting;
    }

    public function setSpacevisiting(?string $spacevisiting): self
    {
        $this->spacevisiting = $spacevisiting;

        return $this;
    }

    public function getCreateAt(): ?\DateTime
    {
        return $this->create_at;
    }

    public function getCreatedAtAgo(): string{
        return Carbon::instance($this->getCreateAt())->diffForHumans();
    }

    public function setCreateAt(\DateTime $create_at): self
    {
        $this->create_at = $create_at;

        return $this;
    }

    public function getExpireAt(): ?\DateTime
    {
        return $this->expire_at;
    }

    public function setExpireAt(?\DateTime $expire_at): self
    {
        $this->expire_at = $expire_at;

        return $this;
    }

    public function getDateUpdate(): ?\DateTime
    {
        return $this->dateUpdate;
    }

    public function setDateUpdate(?\DateTime $dateUpdate): self
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    public function getModulebubble(): ?string
    {
        return $this->modulebubble;
    }

    public function setModulebubble(string $modulebubble): self
    {
        $this->modulebubble = $modulebubble;

        return $this;
    }

    public function getIdmodule(): ?int
    {
        return $this->idmodule;
    }

    public function setIdmodule(int $idmodule): self
    {
        $this->idmodule = $idmodule;

        return $this;
    }


    /**
     * @return Collection
     */
    public function getBullettes(): Collection
    {
        return $this->bullettes;
    }

    public function addBullette(Bullette $bullette): self
    {
        if (!$this->bullettes->contains($bullette)) {
            $this->bullettes[] = $bullette;
            $bullette->setBulle($this);
        }

        return $this;
    }

    public function removeBullette(Bullette $bullette): self
    {
        if ($this->bullettes->removeElement($bullette)) {
            // set the owning side to null (unless already changed)
            if ($bullette->getBulle() === $this) {
                $bullette->setBulle(null);
            }
        }

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    /**
     * @return Collection<int, Customers>
     */
    public function getCustomer(): Collection
    {
        return $this->customer;
    }

    public function addCustomer(Customers $customer): static
    {
        if (!$this->customer->contains($customer)) {
            $this->customer->add($customer);
            $customer->addBulle($this);
        }

        return $this;
    }

    public function removeCustomer(Customers $customer): static
    {
        if ($this->customer->removeElement($customer)) {
            $customer->removeBulle($this);
        }

        return $this;
    }

}
