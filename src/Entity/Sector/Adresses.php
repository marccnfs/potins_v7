<?php


namespace App\Entity\Sector;


use App\Repository\AdressesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: AdressesRepository::class)]
#[ORM\Table(name:"aff_adresses")]
class Adresses
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups("edit_event")]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Sectors::class, mappedBy: 'adresse')]
    private Collection $sector;

    #[ORM\ManyToOne(targetEntity: Gps::class,inversedBy: 'adresses')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Gps $gps= null;

    #[ORM\Column(length: 25,nullable: true)]
    private ?string $idMap = null;

    #[ORM\Column(length: 10,nullable: true)]
    private ?string $numero = null;

    #[ORM\Column(length: 100,nullable: true)]
    private ?string $nom_voie = null;

    #[ORM\Column(length: 7,nullable: true)]
    private ?string $code_postal = null;

    #[ORM\Column(length: 20,nullable: true)]
    private ?string $insee = null;

    #[ORM\Column(length: 150,nullable: true)]
    private ?string $nom_commune = null;

    #[ORM\Column(type: Types::FLOAT,nullable: true)]
    private ?float $lon = null;

    #[ORM\Column(type: Types::FLOAT,nullable: true)]
    private ?float $lat = null;

    #[ORM\Column(length: 150,nullable: true)]
    private ?string $pays = null;

    #[ORM\Column(length: 150,nullable: true)]
    private ?string $typeadress = null;

    #[ORM\Column(length: 255,nullable: true)]
    #[Groups("edit_event")]
    private ?string $label = null;

    #[ORM\Column(length: 100,nullable: true)]
    private ?string $departement = null;

    #[ORM\Column(length: 3,nullable: true)]
    private ?string $numdepart = null;

    #[ORM\Column(length: 100,nullable: true)]
    private ?string $region = null;

    #[ORM\Column(type: Types::INTEGER,nullable: true)]
    private ?int $choiceadress=1;

    public function __construct()
    {
        $this->sector = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdMap(): ?string
    {
        return $this->idMap;
    }

    public function setIdMap(?string $idMap): self
    {
        $this->idMap = $idMap;

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getNomVoie(): ?string
    {
        return $this->nom_voie;
    }

    public function setNomVoie(?string $nom_voie): self
    {
        $this->nom_voie = $nom_voie;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->code_postal;
    }

    public function setCodePostal(?string $code_postal): self
    {
        $this->code_postal = $code_postal;

        return $this;
    }

    public function getNomCommune(): ?string
    {
        return $this->nom_commune;
    }

    public function setNomCommune(?string $nom_commune): self
    {
        $this->nom_commune = $nom_commune;

        return $this;
    }

    public function getLon(): ?float
    {
        return $this->lon;
    }

    public function setLon(?float $lon): self
    {
        $this->lon = $lon;

        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(?float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): self
    {
        $this->pays = $pays;

        return $this;
    }

    public function getTypeadress(): ?string
    {
        return $this->typeadress;
    }

    public function setTypeadress(string $typeadress): self
    {
        $this->typeadress = $typeadress;

        return $this;
    }

    public function getDepartement(): ?string
    {
        return $this->departement;
    }

    public function setDepartement(?string $departement): self
    {
        $this->departement = $departement;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

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

    public function getNumdepart(): ?string
    {
        return $this->numdepart;
    }

    public function setNumdepart(?string $numdepart): self
    {
        $this->numdepart = $numdepart;

        return $this;
    }

    public function getChoiceadress(): ?int
    {
        return $this->choiceadress;
    }

    public function setChoiceadress(int $choiceadress): self
    {
        $this->choiceadress = $choiceadress;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getSector(): Collection
    {
        return $this->sector;
    }

    public function addSector(Sectors $sector): self
    {
        if (!$this->sector->contains($sector)) {
            $this->sector[] = $sector;
        }

        return $this;
    }

    public function removeSector(Sectors $sector): self
    {
        $this->sector->removeElement($sector);

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


}
