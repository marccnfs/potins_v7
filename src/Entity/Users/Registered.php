<?php


namespace App\Entity\Users;

use App\Entity\Sector\Sectors;
use App\Repository\RegisteredRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Ignore;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegisteredRepository::class)]
#[ORM\Table(name:"aff_registered")]
class Registered
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(nullable: true)]
    private ?string $lastname = null;

    #[ORM\ManyToOne(targetEntity: Sectors::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Sectors $sector = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $birthdate = null;

    #[ORM\Column(nullable: true)]
    private ?string $job = null;

    #[ORM\Column(nullable: true)]
    private ?string $sex = null;

    #[ORM\Column(nullable: true)]
    #[Ignore]
    private ?string $mdpfirst = null;

    #[ORM\Column(unique: true,nullable: true)]
    private ?string $emailCanonical = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $datemaj_at;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active = true;

    #[ORM\Column(nullable: true)]
    private ?string $telephonemobile = null;

    #[ORM\Column(nullable: true)]
    private ?string $codeacces = null;

    public function __construct()
    {
        $this->create_at=new DateTime();
    }

    public function __toString() {
        return '#'.$this->id.' - '.$this->firstname.' '.$this->lastname;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function setBirthdate($birthdate)
    {
        if($birthdate != null) {
            $this->birthdate =  $birthdate;
        }
        return $this;
    }

    public function getJob(): ?string
    {
        return $this->job;
    }

    public function setJob(?string $job): self
    {
        $this->job = $job;

        return $this;
    }

    public function getSex(): ?string
    {
        return $this->sex;
    }

    public function setSex(?string $sex): self
    {
        $this->sex = $sex;

        return $this;
    }

    public function getTelephonemobile(): ?string
    {
        return $this->telephonemobile;
    }

    public function setTelephonemobile($telephonemobile): self
    {
        $this->telephonemobile = $telephonemobile;

        return $this;
    }

    public function getSector(): ?Sectors
    {
        return $this->sector;
    }

    public function setSector(?Sectors $sector): self
    {
        $this->sector = $sector;

        return $this;
    }

        public function getEmailCanonical(): ?string
    {
        return $this->emailCanonical;
    }

    public function setEmailCanonical(string $emailCanonical): self
    {
        $this->emailCanonical = $emailCanonical;

        return $this;
    }

    public function getCreateAt(): ?\DateTime
    {
        return $this->create_at;
    }
    public function setCreateAt(\DateTime $create_at): self
    {
        $this->create_at = $create_at;

        return $this;
    }

    public function getDatemajAt(): ?\DateTime
    {
        return $this->datemaj_at;
    }

    public function setDatemajAt(?\DateTime $datemaj_at): self
    {
        $this->datemaj_at = $datemaj_at;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function getCodeacces(): ?string
    {
        return $this->codeacces;
    }

    public function setCodeacces(string $codeacces): self
    {
        $this->codeacces = $codeacces;

        return $this;
    }

    public function getMdpfirst(): ?string
    {
        return $this->mdpfirst;
    }

    public function setMdpfirst(?string $mdpfirst): static
    {
        $this->mdpfirst = $mdpfirst;

        return $this;
    }
}
