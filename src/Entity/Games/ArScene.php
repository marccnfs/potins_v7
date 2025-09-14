<?php

namespace App\Entity\Games;

use App\Repository\ArSceneRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ArSceneRepository::class)]
#[ORM\Table(name: 'ar_scene')]
class ArScene
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 180)]
    private string $title;


// Fichier MindAR généré pour l’image-cible (.mind) stocké/publie
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mindTargetPath = null;


// Index du target dans le .mind (0 par défaut)
    #[ORM\Column(type: 'integer')]
    private int $targetIndex = 0;


// Modèle 3D associé (URL relative /models/lotus.glb)
    #[ORM\Column(length: 255)]
    private string $modelUrl;


// Son optionnel (/audio/water.mp3)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $soundUrl = null;


// Appartenance participant (id user / null si public)
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $ownerId = null;


    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getMindTargetPath(): ?string
    {
        return $this->mindTargetPath;
    }

    public function setMindTargetPath(?string $mindTargetPath): static
    {
        $this->mindTargetPath = $mindTargetPath;

        return $this;
    }

    public function getTargetIndex(): ?int
    {
        return $this->targetIndex;
    }

    public function setTargetIndex(int $targetIndex): static
    {
        $this->targetIndex = $targetIndex;

        return $this;
    }

    public function getModelUrl(): ?string
    {
        return $this->modelUrl;
    }

    public function setModelUrl(string $modelUrl): static
    {
        $this->modelUrl = $modelUrl;

        return $this;
    }

    public function getSoundUrl(): ?string
    {
        return $this->soundUrl;
    }

    public function setSoundUrl(?string $soundUrl): static
    {
        $this->soundUrl = $soundUrl;

        return $this;
    }

    public function getOwnerId(): ?int
    {
        return $this->ownerId;
    }

    public function setOwnerId(?int $ownerId): static
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

}
