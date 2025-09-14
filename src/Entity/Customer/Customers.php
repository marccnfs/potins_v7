<?php

namespace App\Entity\Customer;


use App\Entity\Admin\NumClients;
use App\Entity\Admin\PreOrderResa;
use App\Entity\Bulles\Bulle;
use App\Entity\Member\Activmember;
use App\Entity\UserMap\Heuristiques;
use App\Entity\Users\ProfilUser;
use App\Repository\CustomersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;


#[ORM\Entity(repositoryClass: CustomersRepository::class)]
#[ORM\Table(name:"aff_customers")]
class Customers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[OneToOne(targetEntity: NumClients::class, cascade: ['persist'])]
    #[JoinColumn(nullable: true)]
    private ?NumClients $numclient;

    #[ORM\OneToMany(targetEntity: PreOrderResa::class, mappedBy: 'customer')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $preorders;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $client = false;

    #[OneToOne(targetEntity: Activmember::class, cascade: ['persist'])]
    #[JoinColumn(nullable: true)]
    private ?Activmember $member;

    #[OneToOne(targetEntity: ProfilUser::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(nullable: false)]
    private ?ProfilUser $profil;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $active = true;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $isMember = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $charte = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $charte_at;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $deleted = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $datemaj_at;

    #[ORM\Column(name: 'email', nullable: true)]
    private ?string $emailcontact;

    #[OneToMany(targetEntity: Heuristiques::class, mappedBy: 'customer')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $heuristique;

    #[ManyToMany(targetEntity: Bulle::class, inversedBy: 'customer')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $bulles;

    #[OneToMany(mappedBy: 'customer', targetEntity: Services::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $services;

    public function __construct()
    {
        $this->create_at=new DateTime();
        $this->heuristique = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->preorders = new ArrayCollection();
        $this->bulles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isClient(): ?bool
    {
        return $this->client;
    }

    public function setClient(bool $client): self
    {
        $this->client = $client;

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

    public function isCharte(): ?bool
    {
        return $this->charte;
    }

    public function setCharte(bool $charte): self
    {
        $this->charte = $charte;

        return $this;
    }

    public function getCharteAt(): ?\DateTime
    {
        return $this->charte_at;
    }

    public function setCharteAt(?\DateTime $charte_at): self
    {
        $this->charte_at = $charte_at;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

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

    public function getDatemajAt(): ?\DateTime
    {
        return $this->datemaj_at;
    }

    public function setDatemajAt(?\DateTime $datemaj_at): self
    {
        $this->datemaj_at = $datemaj_at;

        return $this;
    }

    public function getEmailcontact(): ?string
    {
        return $this->emailcontact;
    }

    public function setEmailcontact(?string $emailcontact): self
    {
        $this->emailcontact = $emailcontact;

        return $this;
    }

    public function getNumclient(): ?NumClients
    {
        return $this->numclient;
    }

    public function setNumclient(NumClients $numclient): self
    {
        $this->numclient = $numclient;

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

    public function getProfil(): ?ProfilUser
    {
        return $this->profil;
    }

    public function setProfil(ProfilUser $profil): self
    {
        $this->profil = $profil;

        return $this;
    }

    /**
     * @return Collection<int, Heuristiques>
     */
    public function getHeuristique(): Collection
    {
        return $this->heuristique;
    }

    public function addHeuristique(Heuristiques $heuristique): self
    {
        if (!$this->heuristique->contains($heuristique)) {
            $this->heuristique->add($heuristique);
            $heuristique->setCustomer($this);
        }

        return $this;
    }

    public function removeHeuristique(Heuristiques $heuristique): self
    {
        if ($this->heuristique->removeElement($heuristique)) {
            // set the owning side to null (unless already changed)
            if ($heuristique->getCustomer() === $this) {
                $heuristique->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Services>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Services $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setCustomer($this);
        }

        return $this;
    }

    public function removeService(Services $service): self
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getCustomer() === $this) {
                $service->setCustomer(null);
            }
        }

        return $this;
    }

    public function isIsMember(): ?bool
    {
        return $this->isMember;
    }

    public function setIsMember(bool $isMember): self
    {
        $this->isMember = $isMember;

        return $this;
    }

    /**
     * @return Collection<int, PreOrderResa>
     */
    public function getPreorders(): Collection
    {
        return $this->preorders;
    }

    public function addPreorder(PreOrderResa $preorder): self
    {
        if (!$this->preorders->contains($preorder)) {
            $this->preorders->add($preorder);
            $preorder->setCustomer($this);
        }

        return $this;
    }

    public function removePreorder(PreOrderResa $preorder): self
    {
        if ($this->preorders->removeElement($preorder)) {
            // set the owning side to null (unless already changed)
            if ($preorder->getCustomer() === $this) {
                $preorder->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Bulle>
     */
    public function getBulles(): Collection
    {
        return $this->bulles;
    }

    public function addBulle(Bulle $bulle): static
    {
        if (!$this->bulles->contains($bulle)) {
            $this->bulles->add($bulle);
        }

        return $this;
    }

    public function removeBulle(Bulle $bulle): static
    {
        $this->bulles->removeElement($bulle);

        return $this;
    }

    public function isMember(): ?bool
    {
        return $this->isMember;
    }
}
