<?php


namespace App\Entity\Admin;

use App\Entity\Agenda\Subscription;
use App\Entity\Media\DocEvent;
use App\Entity\Media\Docstore;
use App\Entity\Users\Registered;
use App\Repository\OrderProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: OrderProductsRepository::class)]
#[ORM\Table(name:"aff_orderproduct")]
class OrderProducts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Orders::class, inversedBy: 'listproducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Orders $order= null;

    #[ORM\ManyToOne(targetEntity: Products::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product= null;

    #[ORM\OneToOne(targetEntity: Subscription::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Subscription $subscription= null;

    #[ORM\OneToOne(targetEntity: Registered::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Registered $registered= null;

    #[ORM\OneToMany(mappedBy:'product', targetEntity: DocStore::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $docs;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $multiple = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $priceht = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description=null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $remise=false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $valider=false;

    public function __construct()
    {
        $this->docs = new ArrayCollection();
    }

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

    public function getRemise(): ?bool
    {
        return $this->remise;
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

    public function getOrder(): ?Orders
    {
        return $this->order;
    }

    public function setOrder(?Orders $order): self
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

    public function getValider(): ?bool
    {
        return $this->valider;
    }

    public function setValider(bool $valider): self
    {
        $this->valider = $valider;

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

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): self
    {
        $this->subscription = $subscription;

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

    public function getRegistered(): ?Registered
    {
        return $this->registered;
    }

    public function setRegistered(?Registered $registered): self
    {
        $this->registered = $registered;

        return $this;
    }

    /**
     * @return Collection<int, Docstore>
     */
    public function getDocs(): Collection
    {
        return $this->docs;
    }

    public function addDoc(Docstore $doc): static
    {
        if (!$this->docs->contains($doc)) {
            $this->docs->add($doc);
            $doc->setProduct($this);
        }

        return $this;
    }

    public function removeDoc(Docstore $doc): static
    {
        if ($this->docs->removeElement($doc)) {
            $doc->removeUpload();
            // set the owning side to null (unless already changed)
            if ($doc->getProduct() === $this) {
                $doc->setProduct(null);
            }
        }

        return $this;
    }

}