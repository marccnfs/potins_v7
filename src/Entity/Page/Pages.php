<?php

namespace App\Entity\Page;

use App\Repository\PagesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Validator\Constraints as CustomAssert;

#[ORM\Entity(repositoryClass: PagesRepository::class)]
#[ORM\Table(name:"pages")]
class Pages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $created;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $updated;

    #[ORM\Column(length: 190, unique: true,nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 190,nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[CustomAssert\contraintsCheckUrl()]
    private ?string $contenu = null;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }


    public function getTitre(): ?string
    {
        return $this->titre;
    }


    public function setContenu($contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }


    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setCreated($created): static
    {
        $this->created = $created;

        return $this;
    }


    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    public function setUpdated($updated): static
    {
        $this->updated = $updated;

        return $this;
    }


    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

}
