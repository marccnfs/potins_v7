<?php

namespace App\Entity\Marketplace;


use App\Entity\Media\Pict;
use App\Entity\Posts\Article;
use App\Entity\Ressources\Categories;
use App\Repository\PresentsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: PresentsRepository::class)]
#[ORM\Table(
    name:'aff_presents'
)]
class Presents
{
    #[Groups(['present_post:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(nullable: false)]
    private ?string $titre = null;

    #[ORM\ManyToOne(targetEntity: Categories::class,inversedBy: 'ressources')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Categories $categorie= null;

    #[ORM\Column(nullable: true)]
    private ?string $composition = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string  $descriptif = null;

    #[ORM\OneToOne(targetEntity: Article::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Article $htmlcontent;

    #[ORM\ManyToOne(targetEntity: Pict::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Pict $pict = null;

    #[ORM\ManyToMany(targetEntity: GpPresents::class, mappedBy: 'articles')]
    private Collection $gppresents;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $deleted = false;

    #[ORM\Column(nullable: true)]
    private ?string $infos;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $active = true;

    public function __construct()
    {
        $this->gppresents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($null)
    {
        $this->id=null;
        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getCategorie()
    {
        return $this->categorie;
    }

    public function setCategorie(Categories $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getComposition(): ?string
    {
        return $this->composition;
    }

    public function setComposition(?string $composition): self
    {
        $this->composition = $composition;

        return $this;
    }

    public function getDescriptif(): ?string
    {
        return $this->descriptif;
    }

    public function setDescriptif(?string $descriptif): self
    {
        $this->descriptif = $descriptif;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getPict(): ?Pict
    {
        return $this->pict;
    }

    public function setPict(?Pict $pict): self
    {
        $this->pict = $pict;

        return $this;
    }

    public function getInfos(): ?string
    {
        return $this->infos;
    }

    public function setInfos(string $infos): self
    {
        $this->infos = $infos;

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

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function getHtmlcontent(): ?Article
    {
        return $this->htmlcontent;
    }

    public function setHtmlcontent(?Article $htmlcontent): static
    {
        $this->htmlcontent = $htmlcontent;

        return $this;
    }

    /**
     * @return Collection<int, GpPresents>
     */
    public function getGppresents(): Collection
    {
        return $this->gppresents;
    }

    public function addGppresent(GpPresents $gppresent): static
    {
        if (!$this->gppresents->contains($gppresent)) {
            $this->gppresents->add($gppresent);
            $gppresent->addArticle($this);
        }

        return $this;
    }

    public function removeGppresent(GpPresents $gppresent): static
    {
        if ($this->gppresents->removeElement($gppresent)) {
            $gppresent->removeArticle($this);
        }

        return $this;
    }

}
