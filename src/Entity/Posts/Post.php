<?php

namespace App\Entity\Posts;


use App\Entity\Member\Activmember;
use App\Entity\Member\Boardslist;
use App\Entity\Media\Media;
use App\Entity\Module\GpRessources;
use App\Entity\Module\GpReview;
use App\Entity\Module\PostEvent;
use App\Entity\Module\TabpublicationMsgs;
use App\Entity\Sector\Gps;
use App\Entity\UserMap\Taguery;
use App\Repository\PostRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name:'aff_postation')]
#[ORM\Index(columns:['keymodule'])]
#[UniqueEntity(fields: ['slug'])]
#[Vich\Uploadable]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $keymodule;

    #[ORM\OneToOne(targetEntity: TabpublicationMsgs::class, inversedBy: 'post', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?TabpublicationMsgs $tbmessages;

    #[ORM\Column(length: 190,nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $numberPart = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $agePotin = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $subject = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $iframevideo = null;

    #[ORM\ManyToMany(targetEntity: Taguery::class, inversedBy: 'postations')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $tagueries;

    #[ORM\ManyToOne(targetEntity: Activmember::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Activmember $author= null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $allmember = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $private = false;

    #[ORM\ManyToMany(targetEntity: Boardslist::class, inversedBy: 'postmember')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $members;

    #[ORM\OneToMany(targetEntity: PostEvent::class, mappedBy: 'potin')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $events;

    #[ORM\OneToOne(targetEntity: Media::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Media $media;

    #[Vich\UploadableField(mapping: "potins_media", fileNameProperty: "imageName")]
    private ?File $ImageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $link = null;

    #[ORM\ManyToOne(targetEntity: Gps::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Gps $localisation= null;

    #[ORM\OneToOne(targetEntity: GpRessources::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?GpRessources $gpressources= null;

    #[ORM\OneToOne(targetEntity: GpReview::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?GpReview $gpreview= null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $createAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $modifAt;

    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'potin', cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $htmlcontent;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $publied = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $deleted = false;

    #[ORM\Column(length: 190,nullable: true)]
    private ?string $slug = null;



    public function __construct()
    {
        $this->createAt=new DateTime();
        $this->tagueries = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->htmlcontent = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function  __toString()
    {
        return $this->titre;
    }

    public function postSlug(SluggerInterface $slugger)
    {
        if (!$this->slug || '-' === $this->slug) {
            $this->slug = (string) $slugger->slug((string) $this)->lower();
        }
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

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function isAllmember(): ?bool
    {
        return $this->allmember;
    }

    public function setAllmember(bool $allmember): self
    {
        $this->allmember = $allmember;

        return $this;
    }

    public function isPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): self
    {
        $this->private = $private;

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

    public function getModifAt(): ?\DateTime
    {
        return $this->modifAt;
    }

    public function setModifAt(?\DateTime $modifAt): self
    {
        $this->modifAt = $modifAt;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function isPublied(): ?bool
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

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
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

    public function getTbmessages(): ?TabpublicationMsgs
    {
        return $this->tbmessages;
    }

    public function setTbmessages(?TabpublicationMsgs $tbmessages): self
    {
        $this->tbmessages = $tbmessages;

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

    public function getAuthor(): ?Activmember
    {
        return $this->author;
    }

    public function setAuthor(?Activmember $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, Boardslist>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Boardslist $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }

    public function removeMember(Boardslist $member): self
    {
        $this->members->removeElement($member);

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

    public function getLocalisation(): ?Gps
    {
        return $this->localisation;
    }

    public function setLocalisation(?Gps $localisation): self
    {
        $this->localisation = $localisation;

        return $this;
    }

    /**
     * @return Collection<int, PostEvent>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(PostEvent $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setPotin($this);
        }

        return $this;
    }

    public function removeEvent(PostEvent $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getPotin() === $this) {
                $event->setPotin(null);
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

    public function getAgePotin(): ?int
    {
        return $this->agePotin;
    }

    public function setAgePotin(?int $agePotin): self
    {
        $this->agePotin = $agePotin;

        return $this;
    }

    public function getGpressources(): ?GpRessources
    {
        return $this->gpressources;
    }

    public function setGpressources(?GpRessources $gpressources): static
    {
        $this->gpressources = $gpressources;

        return $this;
    }

    public function getGpreview(): ?GpReview
    {
        return $this->gpreview;
    }

    public function setGpreview(?GpReview $gpreview): static
    {
        $this->gpreview = $gpreview;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getHtmlcontent(): Collection
    {
        return $this->htmlcontent;
    }

    public function addHtmlcontent(Article $htmlcontent): static
    {
        if (!$this->htmlcontent->contains($htmlcontent)) {
            $this->htmlcontent->add($htmlcontent);
            $htmlcontent->setPotin($this);
        }

        return $this;
    }

    public function removeHtmlcontent(Article $htmlcontent): static
    {
        if ($this->htmlcontent->removeElement($htmlcontent)) {
            // set the owning side to null (unless already changed)
            if ($htmlcontent->getPotin() === $this) {
                $htmlcontent->setPotin(null);
            }
        }

        return $this;
    }

    public function getIframevideo(): ?string
    {
        return $this->iframevideo;
    }

    public function setIframevideo(?string $iframevideo): static
    {
        $this->iframevideo = $iframevideo;

        return $this;
    }

    public function setImageFile(?File $file = null): void
    {
        $this->ImageFile = $file;
    }

    public function getImageFile(): ?File
    {
        return $this->ImageFile;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;

        return $this;
    }

}
