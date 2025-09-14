<?php

namespace App\Entity\Admin;


use App\Entity\Customer\Avantages;
use App\Repository\WbordersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: WbordersRepository::class)]
#[ORM\Table(name:"aff_Wborders")]
#[UniqueEntity(fields: ['numcommande'])]
class Wborders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Wbcustomers::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Wbcustomers $wbcustomer= null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $valider=false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $date;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $modifdate;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $numcommande = null;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: WbOrderProducts::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $products;

    #[ORM\Column]
    #[ORM\JoinColumn(nullable: true)]
    private ?string $state = null;

    #[ORM\OneToOne(targetEntity: Avantages::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Avantages $avantage= null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $totalht = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $totalttc = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $totaltva = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $periodicity=false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $encours=false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $startprd;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $endprd;

    #[ORM\Column(length: 5)]
    #[ORM\JoinColumn(nullable: true)]
    private ?string $periodefac = null;

    #[ORM\Column(length: 5)]
    #[ORM\JoinColumn(nullable: true)]
    private ?string $dayfact = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $acceptorder=false;


    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->date=new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getModifdate(): ?\DateTime
    {
        return $this->modifdate;
    }

    public function setModifdate(?\DateTime $modifdate): self
    {
        $this->modifdate = $modifdate;

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

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

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

    public function getWbcustomer(): ?Wbcustomers
    {
        return $this->wbcustomer;
    }

    public function setWbcustomer(?Wbcustomers $wbcustomer): self
    {
        $this->wbcustomer = $wbcustomer;

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

    public function getEncours(): ?bool
    {
        return $this->encours;
    }

    public function setEncours(bool $encours): self
    {
        $this->encours = $encours;

        return $this;
    }

    public function getPeriodefac(): ?string
    {
        return $this->periodefac;
    }

    public function setPeriodefac(?string $periodefac): self
    {
        $this->periodefac = $periodefac;

        return $this;
    }

    public function getDayfact(): ?string
    {
        return $this->dayfact;
    }

    public function setDayfact(?string $dayfact): self
    {
        $this->dayfact = $dayfact;

        return $this;
    }

    public function getAcceptorder(): ?bool
    {
        return $this->acceptorder;
    }

    public function setAcceptorder(bool $acceptorder): self
    {
        $this->acceptorder = $acceptorder;

        return $this;
    }

    public function getStartprd(): ?\DateTime
    {
        return $this->startprd;
    }

    public function setStartprd(?\DateTime $startprd): self
    {
        $this->startprd = $startprd;

        return $this;
    }

    public function getEndprd(): ?\DateTime
    {
        return $this->endprd;
    }

    public function setEndprd(?\DateTime $endprd): self
    {
        $this->endprd = $endprd;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(WbOrderProducts $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setOrder($this);
        }

        return $this;
    }

    public function removeProduct(WbOrderProducts $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getOrder() === $this) {
                $product->setOrder(null);
            }
        }

        return $this;
    }

    public function getPeriodicity(): ?bool
    {
        return $this->periodicity;
    }

    public function setPeriodicity(bool $periodicity): self
    {
        $this->periodicity = $periodicity;

        return $this;
    }

    public function isValider(): ?bool
    {
        return $this->valider;
    }

    public function isPeriodicity(): ?bool
    {
        return $this->periodicity;
    }

    public function isEncours(): ?bool
    {
        return $this->encours;
    }

    public function isAcceptorder(): ?bool
    {
        return $this->acceptorder;
    }

}