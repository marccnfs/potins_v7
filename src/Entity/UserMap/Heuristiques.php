<?php

namespace App\Entity\UserMap;


use App\Entity\Customer\Customers;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name:"aff_heuristique")]
class Heuristiques
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Customers::class, inversedBy: 'heuristique')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customers $customer= null;

    #[ORM\Column(nullable: true)]
    private ?string $sem=null;

    #[ORM\Column(length: 10,nullable: true)]
    private ?string $color=null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $dateinsert;

    #[ORM\Column(nullable: true)]
    private ?string $binarycolor=null;

    public function __construct($customer)
    {
        $this->dateinsert=new DateTime();
        $this->customer=$customer;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSem(): ?string
    {
        return $this->sem;
    }

    public function setSem(?string $sem): self
    {
        $this->sem = $sem;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getDateinsert(): ?\DateTime
    {
        return $this->dateinsert;
    }

    public function setDateinsert(\DateTime $dateinsert): self
    {
        $this->dateinsert = $dateinsert;

        return $this;
    }

    public function getBinarycolor(): ?string
    {
        return $this->binarycolor;
    }

    public function setBinarycolor(?string $binarycolor): self
    {
        $this->binarycolor = $binarycolor;

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

}