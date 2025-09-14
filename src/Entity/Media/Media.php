<?php

namespace App\Entity\Media;

use App\Repository\MediaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\Table(name:"aff_media")]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'idmedia', targetEntity: Imagejpg::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $imagejpg;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $extension = null;

    #[ORM\OneToOne(targetEntity: Pdfstore::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Pdfstore $pdfstore;

    public function __construct()
    {

        $this->imagejpg = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection
     */
    public function getImagejpg(): Collection
    {
        return $this->imagejpg;
    }

    public function addImagejpg(Imagejpg $imagejpg): self
    {
        if (!$this->imagejpg->contains($imagejpg)) {
            $this->imagejpg[] = $imagejpg;
            $imagejpg->setIdmedia($this);
        }

        return $this;
    }

    public function removeImagejpg(Imagejpg $imagejpg): self
    {
        if ($this->imagejpg->contains($imagejpg)) {
            $this->imagejpg->removeElement($imagejpg);
            // set the owning side to null (unless already changed)
            if ($imagejpg->getIdmedia() === $this) {
                $imagejpg->setIdmedia(null);
            }
        }

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(?string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getPdfstore(): ?PdfStore
    {
        return $this->pdfstore;
    }

    public function setPdfstore(?PdfStore $pdfstore): self
    {
        $this->pdfstore = $pdfstore;

        return $this;
    }
}