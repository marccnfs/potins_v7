<?php

namespace App\Entity\Sector;

use App\Entity\UserMap\Hits;
use App\Entity\Boards\Board;
use App\Repository\GpsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\String\Slugger\SluggerInterface;


#[ORM\Entity(repositoryClass: GpsRepository::class)]
#[ORM\Table(name:"aff_gps")]
#[UniqueEntity(
    fields: ['nameloc','slugcity']
)]
class Gps
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $namefile = null;

    #[ORM\Column(length: 150,nullable: true)]
    private ?string $nameloc = null;

    #[ORM\Column(length: 150,nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 190,nullable: true)]
    private ?string $slugcity = null;

    #[ORM\Column(length: 5,nullable: true)]
    private ?string $code = null;

    #[ORM\Column(length: 5,nullable: true)]
    private ?string $insee = null;

    #[ORM\Column(length: 150,nullable: true)]
    private ?string $namecodep = null;

    #[ORM\Column(type: Types::FLOAT,nullable: true)]
    private ?float $lonloc = null;

    #[ORM\Column(type: Types::FLOAT,nullable: true)]
    private ?float $latloc = null;

    #[ORM\Column(type: Types::FLOAT,nullable: true)]
    private ?float $perimeter = null;

    #[ORM\OneToMany(mappedBy: 'gps', targetEntity: Adresses::class)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $adresses;

    #[ORM\OneToMany(mappedBy: 'gps', targetEntity: Hits::class)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $hits;

    #[ORM\OneToMany(mappedBy: 'locality', targetEntity: Board::class)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $boards;

    public function __construct()
    {
        $this->adresses = new ArrayCollection();
        $this->hits = new ArrayCollection();
        $this->boards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function  __toString(): string
    {
        return $this->getNameloc();
    }

    public function gpsSlug(SluggerInterface $slugger)
    {
        if (!$this->slugcity || '-' === $this->slugcity) {
            $this->slugcity = (string) $slugger->slug((string) $this)->lower();
        }
    }

    public function getSlugcity(): ?string
    {
        return $this->slugcity;
    }

    public function getNameloc(): ?string
    {
        return $this->nameloc;
    }

    public function setNameloc(?string $nameloc): self
    {
        $this->nameloc = $nameloc;

        return $this;
    }

    public function getNamefile(): ?string
    {
        return $this->namefile;
    }

    public function setNamefile(?string $namefile): self
    {
        $this->namefile = $namefile;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function setSlugcity(string $slugcity): self
    {
        $this->slugcity = $slugcity;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getInsee(): ?string
    {
        return $this->insee;
    }

    public function setInsee(?string $insee): self
    {
        $this->insee = $insee;

        return $this;
    }

    public function getNamecodep(): ?string
    {
        return $this->namecodep;
    }

    public function setNamecodep(?string $namecodep): self
    {
        $this->namecodep = $namecodep;

        return $this;
    }

    public function getLonloc(): ?float
    {
        return $this->lonloc;
    }

    public function setLonloc(?float $lonloc): self
    {
        $this->lonloc = $lonloc;

        return $this;
    }

    public function getLatloc(): ?float
    {
        return $this->latloc;
    }

    public function setLatloc(?float $latloc): self
    {
        $this->latloc = $latloc;

        return $this;
    }

    public function getPerimeter(): ?float
    {
        return $this->perimeter;
    }

    public function setPerimeter(?float $perimeter): self
    {
        $this->perimeter = $perimeter;

        return $this;
    }

    /**
     * @return Collection<int, Adresses>
     */
    public function getAdresses(): Collection
    {
        return $this->adresses;
    }

    public function addAdress(Adresses $adress): self
    {
        if (!$this->adresses->contains($adress)) {
            $this->adresses->add($adress);
            $adress->setGps($this);
        }

        return $this;
    }

    public function removeAdress(Adresses $adress): self
    {
        if ($this->adresses->removeElement($adress)) {
            // set the owning side to null (unless already changed)
            if ($adress->getGps() === $this) {
                $adress->setGps(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Hits>
     */
    public function getHits(): Collection
    {
        return $this->hits;
    }

    public function addHit(Hits $hit): self
    {
        if (!$this->hits->contains($hit)) {
            $this->hits->add($hit);
            $hit->setGps($this);
        }

        return $this;
    }

    public function removeHit(Hits $hit): self
    {
        if ($this->hits->removeElement($hit)) {
            // set the owning side to null (unless already changed)
            if ($hit->getGps() === $this) {
                $hit->setGps(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Board>
     */
    public function getBoards(): Collection
    {
        return $this->boards;
    }

    public function addBoard(Board $board): self
    {
        if (!$this->boards->contains($board)) {
            $this->boards->add($board);
            $board->setLocality($this);
        }

        return $this;
    }

    public function removeBoard(Board $board): self
    {
        if ($this->boards->removeElement($board)) {
            // set the owning side to null (unless already changed)
            if ($board->getLocality() === $this) {
                $board->setLocality(null);
            }
        }

        return $this;
    }
}