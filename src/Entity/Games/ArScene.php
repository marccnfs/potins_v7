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

    #[ORM\Column(length: 32)]
    private string $contentType = 'model';

    #[ORM\Column(type: 'float')]
    private float $positionX = 0.0;

    #[ORM\Column(type: 'float')]
    private float $positionY = 0.0;

    #[ORM\Column(type: 'float')]
    private float $positionZ = 0.0;

    #[ORM\Column(type: 'float')]
    private float $rotationX = 0.0;

    #[ORM\Column(type: 'float')]
    private float $rotationY = 0.0;

    #[ORM\Column(type: 'float')]
    private float $rotationZ = 0.0;

    #[ORM\Column(type: 'float')]
    private float $scaleX = 1.0;

    #[ORM\Column(type: 'float')]
    private float $scaleY = 1.0;

    #[ORM\Column(type: 'float')]
    private float $scaleZ = 1.0;

// Son optionnel (/audio/water.mp3)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $soundUrl = null;


// Appartenance participant (id user / null si public)
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $ownerId = null;


    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(length: 64, unique: true, nullable: true)]
    private ?string $shareToken = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->shareToken = bin2hex(random_bytes(8));
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

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): static
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getPositionX(): float
    {
        return $this->positionX;
    }

    public function setPositionX(float $positionX): static
    {
        $this->positionX = $positionX;

        return $this;
    }

    public function getPositionY(): float
    {
        return $this->positionY;
    }

    public function setPositionY(float $positionY): static
    {
        $this->positionY = $positionY;

        return $this;
    }

    public function getPositionZ(): float
    {
        return $this->positionZ;
    }

    public function setPositionZ(float $positionZ): static
    {
        $this->positionZ = $positionZ;

        return $this;
    }

    public function getRotationX(): float
    {
        return $this->rotationX;
    }

    public function setRotationX(float $rotationX): static
    {
        $this->rotationX = $rotationX;

        return $this;
    }

    public function getRotationY(): float
    {
        return $this->rotationY;
    }

    public function setRotationY(float $rotationY): static
    {
        $this->rotationY = $rotationY;

        return $this;
    }

    public function getRotationZ(): float
    {
        return $this->rotationZ;
    }

    public function setRotationZ(float $rotationZ): static
    {
        $this->rotationZ = $rotationZ;

        return $this;
    }

    public function getScaleX(): float
    {
        return $this->scaleX;
    }

    public function setScaleX(float $scaleX): static
    {
        $this->scaleX = $scaleX;

        return $this;
    }

    public function getScaleY(): float
    {
        return $this->scaleY;
    }

    public function setScaleY(float $scaleY): static
    {
        $this->scaleY = $scaleY;

        return $this;
    }

    public function getScaleZ(): float
    {
        return $this->scaleZ;
    }

    public function setScaleZ(float $scaleZ): static
    {
        $this->scaleZ = $scaleZ;

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

    public function getShareToken(): string
    {
        if ($this->shareToken === null) {
            $this->shareToken = bin2hex(random_bytes(8));
        }

        return $this->shareToken;
    }

    public function setShareToken(string $shareToken): static
    {
        $this->shareToken = $shareToken;

        return $this;
    }

}
