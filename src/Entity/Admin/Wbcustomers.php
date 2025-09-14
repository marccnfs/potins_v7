<?php


namespace App\Entity\Admin;

use App\Entity\Boards\Board;
use App\Entity\Customer\Customers;
use App\Repository\WbcustomersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: WbcustomersRepository::class)]
#[ORM\Table(name:"aff_wbcustomers")]
#[UniqueEntity(fields: ['numero'])]
class Wbcustomers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $numero = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $ordre = null;

    #[ORM\OneToOne(targetEntity: Board::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Board $board= null;

    #[ORM\OneToOne(targetEntity: Customers::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Customers $customer= null;

    #[ORM\OneToMany(mappedBy: 'wbcustomer', targetEntity: Wborders::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $orders;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getOrdre(): ?float
    {
        return $this->ordre;
    }

    public function setOrdre(float $ordre): self
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * @return Collection<int, Wborders>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Wborders $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setWbcustomer($this);
        }

        return $this;
    }

    public function removeOrder(Wborders $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getWbcustomer() === $this) {
                $order->setWbcustomer(null);
            }
        }

        return $this;
    }

    public function getBoard(): ?Board
    {
        return $this->board;
    }

    public function setBoard(?Board $board): self
    {
        $this->board = $board;

        return $this;
    }

    public function getCustomer(): ?Customers
    {
        return $this->customer;
    }

    public function setCustomer(Customers $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

}