<?php


namespace App\Entity\Admin;


use App\Entity\Customer\Customers;
use App\Repository\NumClientsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: NumClientsRepository::class)]
#[ORM\Table(name:"aff_numclient")]
#[UniqueEntity(fields: ['numero'])]
class NumClients
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $numero = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $ordre = null;

    #[ORM\OneToOne(targetEntity: Customers::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Customers $idcustomer= null;

    #[ORM\OneToMany(mappedBy: 'numclient', targetEntity: Orders::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $orders;


    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    public function increaseClient()
    {
        $this->numero++;
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
     * @return Collection
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Orders $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setNumclient($this);
        }

        return $this;
    }

    public function removeOrder(Orders $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getNumclient() === $this) {
                $order->setNumclient(null);
            }
        }

        return $this;
    }

    public function getIdcustomer(): ?Customers
    {
        return $this->idcustomer;
    }

    public function setIdcustomer(?Customers $idcustomer): self
    {
        $this->idcustomer = $idcustomer;

        return $this;
    }

}