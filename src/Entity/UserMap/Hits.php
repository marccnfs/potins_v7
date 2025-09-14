<?php

namespace App\Entity\UserMap;

use App\Entity\Sector\Gps;
use App\Entity\Boards\Board;
use App\Repository\HitsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;



#[ORM\Entity(repositoryClass: HitsRepository::class)]
#[ORM\Table(name:"aff_hits")]
class Hits
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Board::class, mappedBy: 'hits')]
    private ?Board $board = null;

    #[ORM\ManyToOne(targetEntity: Gps::class, inversedBy: 'hits')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Gps $gps= null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $publi=0;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $liked=0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $lastdayshow;

    #[ORM\OneToMany(targetEntity: Tagcat::class, mappedBy: 'hits')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $catag;

    public function __construct()
    {
        $this->catag = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPubli(): ?int
    {
        return $this->publi;
    }

    public function setPubli(int $publi): self
    {
        $this->publi = $publi;

        return $this;
    }

    public function getLiked(): ?int
    {
        return $this->liked;
    }

    public function setLiked(int $liked): self
    {
        $this->liked = $liked;

        return $this;
    }

    public function getGps(): ?Gps
    {
        return $this->gps;
    }

    public function setGps(?Gps $gps): self
    {
        $this->gps = $gps;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getCatag(): Collection
    {
        return $this->catag;
    }

    public function addCatag(Tagcat $catag): self
    {
        if (!$this->catag->contains($catag)) {
            $this->catag[] = $catag;
            $catag->setHits($this);
        }

        return $this;
    }

    public function removeCatag(Tagcat $catag): self
    {
        if ($this->catag->removeElement($catag)) {
            // set the owning side to null (unless already changed)
            if ($catag->getHits() === $this) {
                $catag->setHits(null);
            }
        }

        return $this;
    }

    public function getLastdayshow(): ?\DateTime
    {
        return $this->lastdayshow;
    }

    public function setLastdayshow(?\DateTime $lastdayshow): self
    {
        $this->lastdayshow = $lastdayshow;

        return $this;
    }

    public function getBoard(): ?Board
    {
        return $this->board;
    }

    public function setBoard(?Board $board): self
    {
        $this->board = $board;

        return $this;
    }

}
