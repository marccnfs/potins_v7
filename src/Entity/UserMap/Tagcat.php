<?php


namespace App\Entity\UserMap;


use App\Repository\TagcatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: TagcatRepository::class)]
#[ORM\Table(name:"aff_tagcat")]
#[UniqueEntity(fields: ['namewebsite'])]
class Tagcat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(nullable: false)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Taguery::class, inversedBy: 'catag')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $tagueries;

    #[ORM\ManyToOne(targetEntity: Hits::class, inversedBy: 'catag')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Hits $hits= null;


    #[ORM\Column(type: Types::INTEGER)]
    private ?int $score=0;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $ponderation=0;


    public function __construct()
    {
        $this->tagueries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getPonderation(): ?float
    {
        return $this->ponderation;
    }

    public function setPonderation(float $ponderation): self
    {
        $this->ponderation = $ponderation;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getHits(): ?Hits
    {
        return $this->hits;
    }

    public function setHits(?Hits $hits): self
    {
        $this->hits = $hits;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getTagueries(): Collection
    {
        return $this->tagueries;
    }

    public function addTaguery(Taguery $taguery): self
    {
        if (!$this->tagueries->contains($taguery)) {
            $this->tagueries[] = $taguery;
        }

        return $this;
    }

    public function removeTaguery(Taguery $taguery): self
    {
        $this->tagueries->removeElement($taguery);

        return $this;
    }

}