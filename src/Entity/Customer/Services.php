<?php


namespace App\Entity\Customer;

use App\Entity\Admin\Products;
use App\Repository\ServicesRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ServicesRepository::class)]
#[ORM\Table(name:"aff_services")]
class Services
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Products::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $products= null;

    #[ORM\Column(nullable: false)]
    private ?string $namemodule;

    #[ORM\ManyToOne(targetEntity: Customers::class, inversedBy: 'services')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customers $customer= null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $datestart_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $dateend_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $datemaj_at;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $active = true;


    public function __construct()
    {
        $this->create_at=new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDatestartAt(): ?\DateTime
    {
        return $this->datestart_at;
    }

    public function setDatestartAt(\DateTime $datestart_at): self
    {
        $this->datestart_at = $datestart_at;

        return $this;
    }

    public function getDateendAt(): ?\DateTime
    {
        return $this->dateend_at;
    }

    public function setDateendAt(\DateTime $dateend_at): self
    {
        $this->dateend_at = $dateend_at;

        return $this;
    }

    public function getDatemajAt(): ?\DateTime
    {
        return $this->datemaj_at;
    }

    public function setDatemajAt(?\DateTime  $datemaj_at): self
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

    public function getCustomer(): ?Customers
    {
        return $this->customer;
    }

    public function setCustomer(?Customers $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getNamemodule(): ?string
    {
        return $this->namemodule;
    }

    public function setNamemodule(string $namemodule): self
    {
        $this->namemodule = $namemodule;

        return $this;
    }

    public function getProducts(): ?Products
    {
        return $this->products;
    }

    public function setProducts(?Products $products): self
    {
        $this->products = $products;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

}