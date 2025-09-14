<?php

namespace App\Entity\Ressources;


use App\Entity\Media\Pict;
use App\Entity\Module\GpRessources;
use App\Entity\Posts\Article;
use App\Repository\RessourcesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;



#[ORM\Entity(repositoryClass: RessourcesRepository::class)]
#[ORM\Table(name:'aff_ressources')]
#[ORM\Index(name: 'find_keymodule', columns: ['keymodule'])]
#[Vich\Uploadable]
class Ressources
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $keymodule = null;

    #[ORM\Column(nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(nullable: true)]
    private ?string $htmltitre = null;

    #[ORM\ManyToOne(targetEntity: Categories::class,inversedBy: 'ressources')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Categories $categorie= null;

    #[ORM\Column(nullable: true)]
    private ?string $composition = null;

    #[ORM\Column(nullable: true)]
    private ?string $label = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string  $descriptif = null;

    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: "ressource", cascade: ["persist", "remove"])]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $htmlcontent;

    #[ORM\Column(nullable: true)]
    private ?string $infos;

    #[ORM\ManyToOne(targetEntity: Pict::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Pict $pict = null;

    #[Vich\UploadableField(mapping: "ressources_media", fileNameProperty: "imageName")]
    private ?File $ImageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $imageName = null;

    #[ORM\ManyToMany(targetEntity: GpRessources::class, mappedBy: 'articles')]
    private Collection $gpressources;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $deleted = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $active = true;

    public function __construct()
    {
        $this->gpressources = new ArrayCollection();
        $this->htmlcontent = new ArrayCollection();
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

    public function setTitre(?string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getHtmltitre(): ?string
    {
        return $this->htmltitre;
    }

    public function setHtmltitre(?string $htmltitre): static
    {
        $this->htmltitre = $htmltitre;

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

    public function getKeymodule(): ?string
    {
        return $this->keymodule;
    }

    public function setKeymodule(string $keymodule): self
    {
        $this->keymodule = $keymodule;

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

    /**
     * @return Collection<int, GpRessources>
     */
    public function getGpressources(): Collection
    {
        return $this->gpressources;
    }

    public function addGpressource(GpRessources $gpressource): self
    {
        if (!$this->gpressources->contains($gpressource)) {
            $this->gpressources->add($gpressource);
            $gpressource->addArticle($this);
        }

        return $this;
    }

    public function removeGpressource(GpRessources $gpressource): self
    {
        if ($this->gpressources->removeElement($gpressource)) {
            $gpressource->removeArticle($this);
        }

        return $this;
    }


    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $this->label = $label;

        return $this;
    }



    /**
     * @return Collection<int, Article>
     */
    public function getHtmlcontent(): Collection
    {
        return $this->htmlcontent;
    }

    public function addHtmlcontent(Article $htmlcontent): static
    {
        if (!$this->htmlcontent->contains($htmlcontent)) {
            $this->htmlcontent->add($htmlcontent);
            $htmlcontent->setRessource($this);
        }

        return $this;
    }

    public function removeHtmlcontent(Article $htmlcontent): static
    {
        if ($this->htmlcontent->removeElement($htmlcontent)) {
            // set the owning side to null (unless already changed)
            if ($htmlcontent->getRessource() === $this) {
                $htmlcontent->setRessource(null);
            }
        }

        return $this;
    }

    public function setImageFile(?File $file = null): void
    {
        $this->ImageFile = $file;
    }

    public function getImageFile(): ?File
    {
        return $this->ImageFile;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }

}
