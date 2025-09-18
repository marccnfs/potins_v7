<?php


namespace App\Entity\Users;

use App\Entity\Media\Avatar;
use App\Entity\Sector\Sectors;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
#[ORM\Table(name:"aff_profiluser")]
class ProfilUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Avatar::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Avatar $avatar = null;

    #[ORM\Column(nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(nullable: true)]
    private ?string $lastname = null;

    #[ORM\ManyToOne(targetEntity: Sectors::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Sectors $sector = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $birthdate= null;

    #[ORM\Column(nullable: true)]
    private ?string $job = null;

    #[ORM\Column(nullable: true)]
    private ?string $sex = null;

    #[ORM\Column(nullable: true)]
    #[Ignore]
    private ?string $emailfirst = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Email(message: "The email '{{ value }}' is not a valid email.")]
    private ?string $emailsecours = null;

    #[ORM\Column(nullable: true)]
    private ?string $telephonefixe = null;

    #[ORM\Column(nullable: true)]
    private ?string $telephonemobile = null;

    #[ORM\OneToOne(targetEntity: Contacts::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Contacts $contact;

    public function setBirthdate($birthdate): static
    {
        if($birthdate != null) {
            $this->birthdate =  $birthdate;  //\DateTime::createFromFormat('d/m/Y',
        }
        return $this;
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

    public function getEmailsecours(): ?string
    {
        return $this->emailsecours;
    }

    public function setEmailsecours(?string $emailsecours): self
    {
        $this->emailsecours = $emailsecours;

        return $this;
    }

    public function getTelephonefixe(): ?string
    {
        return $this->telephonefixe;
    }

    public function setTelephonefixe($telephonefixe): self
    {
        $this->telephonefixe = $telephonefixe;

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

    public function getEmailfirst(): ?string
    {
        return $this->emailfirst;
    }

    public function setEmailfirst(?string $emailfirst): self
    {
        $this->emailfirst = $emailfirst;

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

    public function getAvatar(): ?Avatar
    {
        return $this->avatar;
    }

    public function setAvatar(?Avatar $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getContact(): ?Contacts
    {
        return $this->contact;
    }

    public function setContact(?Contacts $contact): static
    {
        $this->contact = $contact;

        return $this;
    }
}
