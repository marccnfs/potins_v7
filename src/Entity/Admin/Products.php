<?php


namespace App\Entity\Admin;

use App\Entity\Media\Pict;
use App\Repository\ProductsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
#[ORM\Table(name:"aff_products_admin")]
class Products
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Pict::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Pict $pict= null;

    #[ORM\ManyToOne(targetEntity: ProductCategories::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?ProductCategories $categorie= null;

    #[ORM\ManyToOne(targetEntity: Tva::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tva $tva= null;

    #[ORM\Column(length: 125, nullable: false)]
    private ?string $name=null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description=null;

    #[ORM\Column(type: Types::FLOAT, nullable: false)]
    private ?float $price = null;

    #[ORM\Column(length: 125, nullable: false)]
    private ?string $unit=null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $disponible=false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $remisable=false;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getDisponible(): ?bool
    {
        return $this->disponible;
    }

    public function setDisponible(bool $disponible): self
    {
        $this->disponible = $disponible;

        return $this;
    }

    public function getRemisable(): ?bool
    {
        return $this->remisable;
    }

    public function setRemisable(bool $remisable): self
    {
        $this->remisable = $remisable;

        return $this;
    }

    public function getPict(): ?Pict
    {
        return $this->pict;
    }

    public function setPict(?Pict $pict): self
    {
        $this->pict = $pict;

        return $this;
    }

    public function getCategorie(): ?ProductCategories
    {
        return $this->categorie;
    }

    public function setCategorie(?ProductCategories $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getTva(): ?Tva
    {
        return $this->tva;
    }

    public function setTva(?Tva $tva): self
    {
        $this->tva = $tva;

        return $this;
    }

    public function isDisponible(): ?bool
    {
        return $this->disponible;
    }

    public function isRemisable(): ?bool
    {
        return $this->remisable;
    }

}