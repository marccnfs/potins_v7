<?php

namespace App\Entity\Media;

use App\Entity\Games\EscapeGame;
use App\Repository\IllustrationRepository;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: IllustrationRepository::class)]
#[Vich\Uploadable]
class Illustration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Vich\UploadableField(mapping: 'participant_images', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\ManyToOne(targetEntity: EscapeGame::class, inversedBy: 'illustrations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EscapeGame $escapeGame = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;


    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if ($imageFile !== null) {
            $this->updatedAt = new \DateTime();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getEscapeGame(): ?EscapeGame
    {
        return $this->escapeGame;
    }

    public function setEscapeGame(?EscapeGame $escapeGame): static
    {
        $this->escapeGame = $escapeGame;

        return $this;
    }
}

