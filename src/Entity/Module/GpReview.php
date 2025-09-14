<?php

namespace App\Entity\Module;

use App\Entity\Media\Media;
use App\Entity\Member\Activmember;
use App\Entity\Posts\Post;
use App\Entity\Ressources\Reviews;
use App\Repository\GpReviewRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: GpReviewRepository::class)]
#[ORM\Table(name:'aff_Gpreview')]

class GpReview
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(length: 190,nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $subject = null;

    #[ORM\ManyToOne(targetEntity: Activmember::class, inversedBy: 'gpreview')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Activmember $author= null;

    #[ORM\OneToOne(targetEntity: Media::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Media $media;

    #[ORM\OneToOne(targetEntity: Post::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $potin;

    #[ORM\ManyToMany(targetEntity: Reviews::class, inversedBy: 'gpreview')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $reviews;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $createAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $datemaj_at;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $publied = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $deleted = false;

    public function __construct()
    {
        $this->createAt=new DateTime();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getCreateAt()
    {
        return $this->createAt;
    }

    public function setCreateAt(): static
    {
        $createAt = new DateTime();
        $this->createAt = $createAt;

        return $this;
    }

    public function getDatemajAt(): DateTime
    {
        return $this->datemaj_at;
    }

    public function setDatemajAt(?\DateTime $datemaj_at): static
    {
        $this->datemaj_at = $datemaj_at;

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

    public function isPublied(): ?bool
    {
        return $this->publied;
    }

    public function setPublied(bool $publied): static
    {
        $this->publied = $publied;

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

    public function getAuthor(): ?Activmember
    {
        return $this->author;
    }

    public function setAuthor(?Activmember $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): static
    {
        $this->media = $media;

        return $this;
    }

    public function getPotin(): ?Post
    {
        return $this->potin;
    }

    public function setPotin(Post $potin): static
    {
        $this->potin = $potin;

        return $this;
    }

    /**
     * @return Collection<int, Reviews>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Reviews $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
        }

        return $this;
    }

    public function removeReview(Reviews $review): static
    {
        $this->reviews->removeElement($review);

        return $this;
    }

}