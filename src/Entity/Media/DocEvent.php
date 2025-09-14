<?php

namespace App\Entity\Media;


use App\Entity\Admin\OrderProducts;
use App\Entity\Module\PostEvent;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity()]
#[ORM\Table(name:'aff_docevent')]

class DocEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(nullable: false)]
    private ?string $fichier = null;

    #[ORM\Column(nullable: false)]
    private ?string $type;

    #[ORM\ManyToOne(targetEntity: OrderProducts::class, inversedBy: 'docs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?OrderProducts $product= null;

    #[ORM\OneToOne(targetEntity: Pict::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Pict $pict = null;

    #[ORM\OneToOne(targetEntity: Docstore::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Docstore $doc = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $deleted = false;


    public function __construct()
    {
        $this->type = "";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFichier(): ?string
    {
        return $this->fichier;
    }

    public function setFichier(string $fichier): static
    {
        $this->fichier = $fichier;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getEvent(): ?PostEvent
    {
        return $this->event;
    }

    public function setEvent(?PostEvent $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getPict(): ?Pict
    {
        return $this->pict;
    }

    public function setPict(?Pict $pict): static
    {
        $this->pict = $pict;

        return $this;
    }

    public function getDoc(): ?Docstore
    {
        return $this->doc;
    }

    public function setDoc(?Docstore $doc): static
    {
        $this->doc = $doc;

        return $this;
    }

    public function getProduct(): ?OrderProducts
    {
        return $this->product;
    }

    public function setProduct(?OrderProducts $product): static
    {
        $this->product = $product;

        return $this;
    }
}