<?php

namespace App\Entity\Boards;


use App\Entity\Media\Background;
use App\Entity\Media\Pict;
use App\Entity\UserMap\Taguery;
use App\Repository\TemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: TemplateRepository::class)]
#[ORM\Table(name: "aff_template")]
#[UniqueEntity(
    fields: ['emailspaceweb']
)]
class Template
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[OneToOne(targetEntity: Pict::class, cascade: ['persist','remove'])]
    #[JoinColumn(nullable: true)]
    private ?Pict $logo;

    #[OneToOne(targetEntity: Background::class, cascade: ['persist','remove'])]
    #[JoinColumn(nullable: true)]
    private ?Background $background;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description;

    #[ManyToMany(targetEntity: Taguery::class,inversedBy: 'template', cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $tagueries;

    #[ORM\Column(nullable: true)]
    private ?string $baseline;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $activities;

    #[ORM\Column(nullable: true)]
    #[Assert\Email(
        message: "The email '{{ value }}' is not a valid email."
    )]
    private ?string $emailspaceweb;

    #[ORM\Column(length: 35,nullable: true)]
    private ?string $telephonespaceweb;

    #[ORM\Column(length: 35,nullable: true)]
    private ?string $telephonemobspaceweb;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $header =false;

    public function __construct()
    {
        $this->tagueries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getBaseline(): ?string
    {
        return $this->baseline;
    }

    public function setBaseline(?string $baseline): self
    {
        $this->baseline = $baseline;

        return $this;
    }

    public function getActivities(): ?string
    {
        return $this->activities;
    }

    public function setActivities(?string $activities): self
    {
        $this->activities = $activities;

        return $this;
    }

    public function getEmailspaceweb(): ?string
    {
        return $this->emailspaceweb;
    }

    public function setEmailspaceweb(?string $emailspaceweb): self
    {
        $this->emailspaceweb = $emailspaceweb;

        return $this;
    }

    public function getTelephonespaceweb(): ?string
    {
        return $this->telephonespaceweb;
    }

    public function setTelephonespaceweb(?string $telephonespaceweb): self
    {
        $this->telephonespaceweb = $telephonespaceweb;

        return $this;
    }

    public function getTelephonemobspaceweb(): ?string
    {
        return $this->telephonemobspaceweb;
    }

    public function setTelephonemobspaceweb(?string $telephonemobspaceweb): self
    {
        $this->telephonemobspaceweb = $telephonemobspaceweb;

        return $this;
    }

    public function isHeader(): ?bool
    {
        return $this->header;
    }

    public function setHeader(bool $header): self
    {
        $this->header = $header;

        return $this;
    }

    public function getLogo(): ?Pict
    {
        return $this->logo;
    }

    public function setLogo(?Pict $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getBackground(): ?Background
    {
        return $this->background;
    }

    public function setBackground(?Background $background): self
    {
        $this->background = $background;

        return $this;
    }

    /**
     * @return Collection<int, Taguery>
     */
    public function getTagueries(): Collection
    {
        return $this->tagueries;
    }

    public function addTaguery(Taguery $taguery): self
    {
        if (!$this->tagueries->contains($taguery)) {
            $this->tagueries->add($taguery);
        }

        return $this;
    }

    public function removeTaguery(Taguery $taguery): self
    {
        $this->tagueries->removeElement($taguery);

        return $this;
    }

}