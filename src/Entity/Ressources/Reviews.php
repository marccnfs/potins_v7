<?php

namespace App\Entity\Ressources;


use App\Entity\Media\Pict;
use App\Entity\Module\GpReview;
use App\Entity\Posts\Fiche;
use App\Repository\RessourcesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: RessourcesRepository::class)]
#[ORM\Table(name:'aff_review')]

class Reviews
{
    #[Groups(['review_post:read','gp_review_post:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Groups(['review_post:read','gp_review_post:read'])]
    #[ORM\Column(nullable: false)]
    private ?string $titre = null;

    #[Groups(['review_post:read','gp_review_post:read'])]
    #[ORM\Column(nullable: true)]
    private ?string $soustitre = null;

    #[ORM\OneToOne(targetEntity: Fiche::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Fiche $fiche;

    #[Groups(['review_post:read','gp_review_post:read'])]
    #[ORM\ManyToOne(targetEntity: Pict::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Pict $pict = null;

    #[ORM\ManyToMany(targetEntity: GpReview::class, mappedBy: 'reviews')]
    private Collection $gpreview;

    #[Groups(['review_post:read','gp_review_post:read'])]
    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $deleted = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $active = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $type = true;

    public function __construct()
    {
        $this->gpreview = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getSoustitre(): ?string
    {
        return $this->soustitre;
    }

    public function setSoustitre(?string $soustitre): static
    {
        $this->soustitre = $soustitre;

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

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getFiche(): ?Fiche
    {
        return $this->fiche;
    }

    public function setFiche(?Fiche $fiche): static
    {
        $this->fiche = $fiche;

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

    /**
     * @return Collection<int, GpReview>
     */
    public function getGpreview(): Collection
    {
        return $this->gpreview;
    }

    public function addGpreview(GpReview $gpreview): static
    {
        if (!$this->gpreview->contains($gpreview)) {
            $this->gpreview->add($gpreview);
            $gpreview->addReview($this);
        }

        return $this;
    }

    public function removeGpreview(GpReview $gpreview): static
    {
        if ($this->gpreview->removeElement($gpreview)) {
            $gpreview->removeReview($this);
        }

        return $this;
    }

    public function isType(): ?bool
    {
        return $this->type;
    }

    public function setType(bool $type): static
    {
        $this->type = $type;

        return $this;
    }

}
