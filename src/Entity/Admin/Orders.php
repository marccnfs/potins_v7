<?php

namespace App\Entity\Admin;


use App\Entity\Customer\Avantages;
use App\Repository\OrdersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: OrdersRepository::class)]
#[ORM\Table(name:"aff_orders")]
#[UniqueEntity(fields: ['numcommande'])]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: NumClients::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?NumClients $numclient= null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $valider=false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $date;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $modifdate;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $numcommande = null;

    #[ORM\Column(nullable: true)]
    private ?string $state = null;

    #[ORM\OneToOne(targetEntity: Avantages::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Avantages $avantage= null;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderProducts::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $listproducts;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $totalht = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $totalttc = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $totaltva = null;


    public function __construct()
    {
        $this->date=new \DateTime();
        $this->listproducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return strval($this->numcommande);
    }

    public function getValider(): ?bool
    {
        return $this->valider;
    }

    public function setValider(bool $valider): self
    {
        $this->valider = $valider;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getNumcommande(): ?int
    {
        return $this->numcommande;
    }

    public function setNumcommande(int $numcommande): self
    {
        $this->numcommande = $numcommande;

        return $this;
    }

    public function getNumclient(): NumClients
    {
        return $this->numclient;
    }

    public function setNumclient(?NumClients $numclient): self
    {
        $this->numclient = $numclient;

        return $this;
    }

    public function getModifdate(): ?\DateTime
    {
        return $this->modifdate;
    }

    public function setModifdate(?\DateTime $modifdate): self
    {
        $this->modifdate = $modifdate;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getAvantage(): ?Avantages
    {
        return $this->avantage;
    }

    public function setAvantage(?Avantages $avantage): self
    {
        $this->avantage = $avantage;

        return $this;
    }

    public function getTotalht(): ?float
    {
        return $this->totalht;
    }

    public function setTotalht(float $totalht): self
    {
        $this->totalht = $totalht;

        return $this;
    }

    public function getTotalttc(): ?float
    {
        return $this->totalttc;
    }

    public function setTotalttc(float $totalttc): self
    {
        $this->totalttc = $totalttc;

        return $this;
    }

    public function getTotaltva(): ?float
    {
        return $this->totaltva;
    }

    public function setTotaltva(float $totaltva): self
    {
        $this->totaltva = $totaltva;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getListproducts(): Collection
    {
        return $this->listproducts;
    }

    public function addListproduct(OrderProducts $listproduct): self
    {
        if (!$this->listproducts->contains($listproduct)) {
            $this->listproducts[] = $listproduct;
            $listproduct->setOrder($this);
        }
        return $this;
    }

    public function removeListproduct(OrderProducts $listproduct): self
    {
        if ($this->listproducts->removeElement($listproduct)) {
            // set the owning side to null (unless already changed)
            if ($listproduct->getOrder() === $this) {
                $listproduct->setOrder(null);
            }
        }
        return $this;
    }

    public function isValider(): ?bool
    {
        return $this->valider;
    }

}