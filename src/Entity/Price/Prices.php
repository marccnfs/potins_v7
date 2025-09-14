<?php

namespace App\Entity\Price;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name:"aff_prices")]
class Prices
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::FLOAT,nullable: true)]
    private ?float $priceunit = null;

    #[ORM\Column(length: 125,nullable: true)]
    private ?string $infoprice = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $free = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($null)
    {
        $this->id=null;
        return $this;
    }

    public function getPriceunit(): ?float
    {
        return $this->priceunit;
    }

    public function setPriceunit(?float $priceunit): self
    {
        $this->priceunit = $priceunit;

        return $this;
    }

    public function getInfoprice(): ?string
    {
        return $this->infoprice;
    }

    public function setInfoprice(?string $infoprice): self
    {
        $this->infoprice = $infoprice;

        return $this;
    }

    public function getFree(): ?bool
    {
        return $this->free;
    }

    public function setFree(bool $free): self
    {
        $this->free = $free;

        return $this;
    }

    public function isFree(): ?bool
    {
        return $this->free;
    }
}