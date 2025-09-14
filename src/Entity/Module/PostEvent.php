<?php


namespace App\Entity\Module;

use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Admin\PreOrderResa;
use App\Entity\Agenda\Appointments;
use App\Entity\Member\Activmember;
use App\Entity\Media\Media;
use App\Entity\Posts\Post;
use App\Entity\Sector\Sectors;
use App\Entity\UserMap\Taguery;
use App\Entity\Boards\Board;
use App\Repository\PostEventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostEventRepository::class)]
#[ORM\Table(name:'aff_postevent')]
#[ORM\Index(columns:['keymodule'])]

class PostEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups("edit_event")]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(nullable: false)]
    private ?string $keymodule;

    #[ORM\OneToOne(targetEntity: Sectors::class, cascade: ['persist', 'remove'])]
    #[Groups("edit_event")]
    #[ORM\JoinColumn(nullable: true)]
    private ?Sectors $sector;

    #[ORM\ManyToOne(targetEntity: Board::class, inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Board $locatemedia= null;

    #[ORM\OneToMany(targetEntity: Activmember::class, mappedBy: 'events')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $associate;

    #[Groups("edit_event")]
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $numberPart = null;

    #[ORM\OneToMany(targetEntity: PreOrderResa::class, mappedBy: 'event')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $preorders;

    #[ORM\ManyToOne(targetEntity: Activmember::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Activmember $author= null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Post $potin= null;

    #[ORM\OneToOne(targetEntity: Appointments::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups("edit_event")]
    private ?Appointments $appointment;

    #[ORM\Column(length: 190, nullable: false)]
    #[Groups("edit_event")]
    private ?string $titre = null;

    #[ORM\Column]
    #[Groups("edit_event")]
    private ?string $description;

    #[ORM\ManyToMany(targetEntity: Taguery::class, inversedBy: 'postevents')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $tagueries;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\OneToOne(targetEntity: Media::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups("edit_event")]
    private ?Media $media;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $publied = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $datemaj_at;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $deleted = false;

    public function __construct()
    {
        $this->tagueries = new ArrayCollection();
        $this->create_at=new \DateTime();
        $this->datemaj_at=new \DateTime();
        $this->associate = new ArrayCollection();
        $this->preorders = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->titre;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKeymodule(): ?string
    {
        return $this->keymodule;
    }

    public function setKeymodule(string $keymodule): self
    {
        $this->keymodule = $keymodule;
        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDatemajAt(): \DateTime
    {
        return $this->datemaj_at;
    }

    public function setDatemajAt(\DateTime $datemaj_at): self
    {
        $this->datemaj_at = $datemaj_at;
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

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;
        return $this;
    }

    public function getAppointment(): ?Appointments
    {
        return $this->appointment;
    }

    public function setAppointment(Appointments $appointment): self
    {
        $this->appointment = $appointment;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getAssociate(): Collection
    {
        return $this->associate;
    }

    public function isPublied(): ?bool
    {
        return $this->publied;
    }

    public function getPublied(): ?bool
    {
        return $this->publied;
    }

    public function setPublied(bool $publied): self
    {
        $this->publied = $publied;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
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

    public function addAssociate(Activmember $associate): self
    {
        if (!$this->associate->contains($associate)) {
            $this->associate->add($associate);
            $associate->setEvents($this);
        }

        return $this;
    }

    public function removeAssociate(Activmember $associate): self
    {
        if ($this->associate->removeElement($associate)) {
            // set the owning side to null (unless already changed)
            if ($associate->getEvents() === $this) {
                $associate->setEvents(null);
            }
        }

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

    public function getPotin(): ?Post
    {
        return $this->potin;
    }

    public function setPotin(?Post $potin): self
    {
        $this->potin = $potin;

        return $this;
    }

    /**
     * @return Collection<int, PreOrderResa>
     */
    public function getPreorders(): Collection
    {
        return $this->preorders;
    }

    public function addPreorder(PreOrderResa $preorder): self
    {
        if (!$this->preorders->contains($preorder)) {
            $this->preorders->add($preorder);
            $preorder->setEvent($this);
        }

        return $this;
    }

    public function removePreorder(PreOrderResa $preorder): self
    {
        if ($this->preorders->removeElement($preorder)) {
            // set the owning side to null (unless already changed)
            if ($preorder->getEvent() === $this) {
                $preorder->setEvent(null);
            }
        }

        return $this;
    }

    public function getNumberPart(): ?int
    {
        return $this->numberPart;
    }

    public function setNumberPart(?int $numberPart): self
    {
        $this->numberPart = $numberPart;

        return $this;
    }

    public function getLocatemedia(): ?Board
    {
        return $this->locatemedia;
    }

    public function setLocatemedia(?Board $locatemedia): self
    {
        $this->locatemedia = $locatemedia;

        return $this;
    }
}
