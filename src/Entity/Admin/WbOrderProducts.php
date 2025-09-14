<?php


namespace App\Entity\Admin;

use App\Entity\Agenda\Subscription;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name:"aff_wborderproduct")]
class WbOrderProducts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Wborders::class, inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Wborders $order= null;

    #[ORM\ManyToOne(targetEntity: Products::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product= null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $multiple = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $priceht = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description=null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $remise=false;

    #[ORM\OneToOne(targetEntity: Subscription::class, inversedBy: 'wbprodorder', cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Subscription $subscription= null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $valider=false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMultiple(): ?float
    {
        return $this->multiple;
    }

    public function setMultiple(float $multiple): self
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function getPriceht(): ?float
    {
        return $this->priceht;
    }

    public function setPriceht(?float $priceht): self
    {
        $this->priceht = $priceht;

        return $this;
    }

    public function setRemise(bool $remise): self
    {
        $this->remise = $remise;

        return $this;
    }

    public function isRemised(): ?bool
    {
        return $this->remise;
    }

    public function getOrder(): ?Wborders
    {
        return $this->order;
    }

    public function setOrder(?Wborders $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getProduct(): ?Products
    {
        return $this->product;
    }

    public function setProduct(?Products $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): self
    {
        $this->subscription = $subscription;

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

    public function getRemise(): ?bool
    {
        return $this->remise;
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

    public function isRemise(): ?bool
    {
        return $this->remise;
    }

    public function isValider(): ?bool
    {
        return $this->valider;
    }

}
