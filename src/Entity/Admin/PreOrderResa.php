<?php

namespace App\Entity\Admin;

use App\Entity\Customer\Customers;
use App\Entity\Module\PostEvent;
use App\Repository\PreOrderResaRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use GuzzleHttp\Psr7\Message;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: PreOrderResaRepository::class)]
#[ORM\Table(name:"aff_preorderresa")]

class PreOrderResa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PostEvent::class, inversedBy: 'preorders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PostEvent $event= null;

    #[ORM\ManyToOne(targetEntity: Customers::class, inversedBy: 'preorders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customers $customer= null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $valider=false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $create_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $modif_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $resa_at;

    #[Assert\LessThanOrEqual(3,message: 'maximum trois participants')]
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $numberresa = null;

    public function __construct()
    {
        $this->create_at=new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isValider(): ?bool
    {
        return $this->valider;
    }

    public function setValider(bool $valider): self
    {
        $this->valider = $valider;

        return $this;
    }

    public function getCreateAt(): ?DateTime
    {
        return $this->create_at;
    }

    public function setCreateAt(?DateTime $create_at): self
    {
        $this->create_at = $create_at;

        return $this;
    }

    public function getModifAt(): ?DateTime
    {
        return $this->modif_at;
    }

    public function setModifAt(?DateTime $modif_at): self
    {
        $this->modif_at = $modif_at;

        return $this;
    }

    public function getResaAt(): ?DateTime
    {
        return $this->resa_at;
    }

    public function setResaAt(?DateTime $resa_at): self
    {
        $this->resa_at = $resa_at;

        return $this;
    }

    public function getEvent(): ?PostEvent
    {
        return $this->event;
    }

    public function setEvent(?PostEvent $event): self
    {
        $this->event = $event;

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

    public function getNumberresa(): ?int
    {
        return $this->numberresa;
    }

    public function setNumberresa(int $numberresa): self
    {
        $this->numberresa = $numberresa;

        return $this;
    }

}