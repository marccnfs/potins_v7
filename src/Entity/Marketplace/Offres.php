<?php


namespace App\Entity\Marketplace;



use App\Entity\Agenda\Appointments;
use App\Entity\Customer\Transactions;
use App\Entity\Member\Activmember;
use App\Entity\Media\Media;
use App\Entity\Module\TabpublicationMsgs;
use App\Repository\OffresRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\String\Slugger\SluggerInterface;



#[ORM\Entity(repositoryClass: OffresRepository::class)]
#[ORM\Table(name:'aff_offres')]
#[ORM\Index(columns:['keymodule'])]
#[UniqueEntity(fields: ['slug'])]

class Offres
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(nullable: false)]
    private ?string $keymodule;

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $code;

    #[ORM\OneToOne(targetEntity: TabpublicationMsgs::class, inversedBy: 'offre', cascade: ['persist','remove'])]
    private ?TabpublicationMsgs $tbmessages = null;

    #[ORM\OneToOne(targetEntity: Appointments::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Appointments $parution;

    #[ORM\Column(length: 190, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 10,
        max: 190,
        minMessage: 'le titre doit faire au moins {{ limit }} caractères',
        maxMessage: 'le titre doit faire au maximum {{ limit }} caractères',)]
    private ?string $titre;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 10,
        max: 250,
        minMessage: 'le titre doit faire au moins {{ limit }} caractères',
        maxMessage: 'le titre doit faire au maximum {{ limit }} caractères',)]
    private ?string $descriptif;

    #[ORM\ManyToOne(targetEntity: Activmember::class, inversedBy: 'offre')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Activmember $author = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $private = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isotherdest = false;

    #[ORM\Column(nullable: true)]
    private ?string $destinataire;

    #[ORM\OneToOne(targetEntity: Media::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Media $media;

    #[ORM\OneToOne(targetEntity: GpPresents::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?GpPresents $gppresents= null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $createAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $endAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $modifAt;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $deleted = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $publied = true;

    #[ORM\Column(unique: true)]
    private ?string $slug;

    #[ORM\OneToMany(mappedBy: 'offre', targetEntity: Transactions::class)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $transactions;

    public function __construct()
    {
        $this->createAt=new DateTime();
        $this->transactions = new ArrayCollection();
        $this->slug="-";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function  __toString()
    {
        return $this->titre;
    }

    public function offreSlug(SluggerInterface $slugger): void
    {
        if (!$this->slug || '-' === $this->slug) {
            $this->slug = (string) $slugger->slug((string) $this)->lower();
        }
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescriptif(): ?string
    {
        return $this->descriptif;
    }

    public function setDescriptif(?string $descriptif): self
    {
        $this->descriptif = $descriptif;

        return $this;
    }

    public function getCreateAt(): ?\DateTime
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTime $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getEndAt(): ?\DateTime
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTime $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getModifAt(): ?\DateTime
    {
        return $this->modifAt;
    }

    public function setModifAt(?\DateTime $modifAt): self
    {
        $this->modifAt = $modifAt;

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

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }
    public function setPublied(bool $publied): Offres
    {
        $this->publied=$publied;
        return $this;
    }

    public function getPublied(): ?bool
    {
        return $this->publied;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getAuthor(): ?Activmember
    {
        return $this->author;
    }

    public function setAuthor(?Activmember $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): self
    {
        $this->media = $media;

        return $this;
    }

    public function getParution(): ?Appointments
    {
        return $this->parution;
    }

    public function setParution(Appointments $parution): self
    {
        $this->parution = $parution;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transactions $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setOffre($this);
        }

        return $this;
    }

    public function removeTransaction(Transactions $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
            // set the owning side to null (unless already changed)
            if ($transaction->getOffre() === $this) {
                $transaction->setOffre(null);
            }
        }

        return $this;
    }

    public function getIsotherdest(): ?bool
    {
        return $this->isotherdest;
    }

    public function setIsotherdest(bool $isotherdest): self
    {
        $this->isotherdest = $isotherdest;

        return $this;
    }

    public function getDestinataire(): ?string
    {
        return $this->destinataire;
    }

    public function setDestinataire(?string $destinataire): self
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    public function getKeymodule(): ?string
    {
        return $this->keymodule;
    }

    public function setKeymodule(?string $keymodule): self
    {
        $this->keymodule = $keymodule;

        return $this;
    }

    public function getTbmessages(): ?TabpublicationMsgs
    {
        return $this->tbmessages;
    }

    public function setTbmessages(?TabpublicationMsgs $tbmessages): self
    {
        $this->tbmessages = $tbmessages;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function isIsotherdest(): ?bool
    {
        return $this->isotherdest;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function isPublied(): ?bool
    {
        return $this->publied;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function isPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): static
    {
        $this->private = $private;

        return $this;
    }

    public function getGppresents(): ?GpPresents
    {
        return $this->gppresents;
    }

    public function setGppresents(?GpPresents $gppresents): static
    {
        $this->gppresents = $gppresents;

        return $this;
    }

    public function isotherdest(): ?bool
    {
        return $this->isotherdest;
    }

    public function setOtherdest(bool $isotherdest): static
    {
        $this->isotherdest = $isotherdest;

        return $this;
    }

}
