<?php

namespace App\Entity\Sector;


use App\Repository\SectorsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: SectorsRepository::class)]
#[ORM\Table(name:"aff_sector")]
class Sectors
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $codesite;

    #[ORM\ManyToMany(targetEntity: Adresses::class, inversedBy: 'sector', cascade: ['persist','remove'])]
    #[Groups("edit_event")]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $adresse;

    #[ORM\Column(length: 150,nullable: true)]
    private ?string $infosecteur = null;

    #[ORM\Column(length: 8,nullable: true)]
    private ?string $codesector = null;

    public function __construct()
    {
        $this->adresse = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInfosecteur(): ?string
    {
        return $this->infosecteur;
    }

    public function setInfosecteur(?string $infosecteur): self
    {
        $this->infosecteur = $infosecteur;

        return $this;
    }

    public function getCodesector(): ?string
    {
        return $this->codesector;
    }

    public function setCodesector(?string $codesector): self
    {
        $this->codesector = $codesector;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getAdresse(): Collection
    {
        return $this->adresse;
    }

    public function addAdresse(Adresses $adresse): self
    {
        if (!$this->adresse->contains($adresse)) {
            $this->adresse[] = $adresse;
            $adresse->addSector($this);
        }

        return $this;
    }

    public function removeAdresse(Adresses $adresse): self
    {
        if ($this->adresse->removeElement($adresse)) {
            $adresse->removeSector($this);
        }

        return $this;
    }

    public function getCodesite(): ?string
    {
        return $this->codesite;
    }

    public function setCodesite(?string $codesite): self
    {
        $this->codesite = $codesite;

        return $this;
    }

}
