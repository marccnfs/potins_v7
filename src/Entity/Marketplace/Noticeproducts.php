<?php


namespace App\Entity\Marketplace;

use App\Entity\UserMap\Taguery;
use App\Repository\NoticeproductsRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: NoticeproductsRepository::class)]
#[ORM\Table(name:'aff_noticeproduct')]
class Noticeproducts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $nameproduct=null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $idproduct=null;

    #[ORM\Column(nullable: true)]
    #[Assert\Url(
        message: "l'url '{{ value }}' n'est pas valide",)]
    private ?string $urlproduct;

    #[ORM\Column]
    private ?string $description=null;

    #[ORM\Column(nullable: true)]
    private ?string $tabcarac;

    #[ORM\OneToOne(targetEntity: DescriptProduct::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?DescriptProduct $htmlcontent;

    #[ORM\ManyToMany(targetEntity: Taguery::class, inversedBy: 'noticeproducts', cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $tagueries;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $price;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $oldprice;

    #[ORM\Column]
    private ?string $unit=null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $disponible = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $remisable = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $createAt;


    public function __construct()
    {
        $this->createAt=new DateTime();
        $this->tagueries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameproduct(): ?string
    {
        return $this->nameproduct;
    }

    public function setNameproduct(string $nameproduct): self
    {
        $this->nameproduct = $nameproduct;

        return $this;
    }

    public function getIdproduct(): ?string
    {
        return $this->idproduct;
    }

    public function setIdproduct(?string $idproduct): self
    {
        $this->idproduct = $idproduct;

        return $this;
    }

    public function getUrlproduct(): ?string
    {
        return $this->urlproduct;
    }

    public function setUrlproduct(?string $urlproduct): self
    {
        $this->urlproduct = $urlproduct;

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

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getOldprice(): ?float
    {
        return $this->oldprice;
    }

    public function setOldprice(?float $oldprice): self
    {
        $this->oldprice = $oldprice;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): self
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

    public function getCreateAt(): ?\DateTime
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTime $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getHtmlcontent(): ?DescriptProduct
    {
        return $this->htmlcontent;
    }

    public function setHtmlcontent(?DescriptProduct $htmlcontent): self
    {
        $this->htmlcontent = $htmlcontent;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getTagueries(): Collection
    {
        return $this->tagueries;
    }

    public function addTaguery(Taguery $taguery): self
    {
        if (!$this->tagueries->contains($taguery)) {
            $this->tagueries[] = $taguery;
        }

        return $this;
    }

    public function removeTaguery(Taguery $taguery): self
    {
        if ($this->tagueries->contains($taguery)) {
            $this->tagueries->removeElement($taguery);
        }

        return $this;
    }

    public function getTabcarac(): ?string
    {
        return $this->tabcarac;
    }

    public function setTabcarac(string $tabcarac): self
    {
        $this->tabcarac = $tabcarac;

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