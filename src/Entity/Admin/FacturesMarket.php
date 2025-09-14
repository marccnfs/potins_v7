<?php


namespace App\Entity\Admin;

use App\Repository\FacturesMarketRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: FacturesMarketRepository::class)]
#[ORM\Table(name:"aff_facturesmarket")]
#[UniqueEntity(fields: ['numfact'])]
class FacturesMarket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Orders::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Orders $orders= null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $valider=false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $montantttc=false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $solde=false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $accompte=false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $datereglement;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $dateraccompte;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $numfact = null;

    #[ORM\Column(type: Types::STRING, length: 180)]
    private ?string $pdfacture = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $create_at;

    public function __construct()
    {
        $this->datereglement = new DateTime();
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

    public function getSolde(): ?bool
    {
        return $this->solde;
    }

    public function setSolde(bool $solde): self
    {
        $this->solde = $solde;

        return $this;
    }

    public function getAccompte(): ?bool
    {
        return $this->accompte;
    }

    public function setAccompte(bool $accompte): self
    {
        $this->accompte = $accompte;

        return $this;
    }

    public function getDatereglement(): ?\DateTime
    {
        return $this->datereglement;
    }

    public function setDatereglement(\DateTime $datereglement): self
    {
        $this->datereglement = $datereglement;

        return $this;
    }

    public function getDateraccompte(): ?\DateTime
    {
        return $this->dateraccompte;
    }

    public function setDateraccompte(\DateTime $dateraccompte): self
    {
        $this->dateraccompte = $dateraccompte;

        return $this;
    }

    public function getNumfact(): ?int
    {
        return $this->numfact;
    }

    public function setNumfact(int $numfact): self
    {
        $this->numfact = $numfact;

        return $this;
    }

    public function getOrders(): ?Orders
    {
        return $this->orders;
    }

    public function setOrders(Orders $orders): self
    {
        $this->orders = $orders;

        return $this;
    }

    public function getPdfacture(): ?string
    {
        return $this->pdfacture;
    }

    public function setPdfacture(?string $pdfacture): self
    {
        $this->pdfacture = $pdfacture;

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

    public function getMontantttc(): ?float
    {
        return $this->montantttc;
    }

    public function setMontantttc(float $montantttc): self
    {
        $this->montantttc = $montantttc;

        return $this;
    }

    public function isValider(): ?bool
    {
        return $this->valider;
    }

    public function isSolde(): ?bool
    {
        return $this->solde;
    }

    public function isAccompte(): ?bool
    {
        return $this->accompte;
    }

    public function isMontantttc(): ?bool
    {
        return $this->montantttc;
    }
}