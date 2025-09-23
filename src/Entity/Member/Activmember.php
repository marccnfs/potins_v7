<?php


namespace App\Entity\Member;


use App\Entity\Customer\Avantages;
use App\Entity\Customer\Customers;
use App\Entity\Customer\Transactions;
use App\Entity\HyperCom\TagAnalytic;
use App\Entity\Marketplace\Offres;
use App\Entity\Module\GpReview;
use App\Entity\Module\PostEvent;
use App\Entity\Notifications\Notifmember;
use App\Entity\Posts\Post;
use App\Entity\Sector\Gps;
use App\Entity\Sector\Sectors;
use App\Entity\UserMap\Taguery;
use App\Repository\ActivMemberRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: ActivMemberRepository::class)]
#[ORM\Table(name:"aff_activmember")]
#[UniqueEntity(fields: ['slug'],)]
class Activmember
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: 'le nom doit faire au moins {{ limit }} caractères',
        maxMessage: 'le nom doit faire au maximum {{ limit }} caractères',)]
    private ?string $name;

    #[ORM\Column(type: Types::JSON, nullable: true )]
    private ?array $permission = [];

    #[ORM\OneToMany(mappedBy: 'member', targetEntity: Boardslist::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $boardslist;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Post::class)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $posts;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Offres::class)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $offre;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: GpReview::class)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $gpreview;

    #[ORM\ManyToOne(targetEntity: PostEvent::class, inversedBy: 'associate')]
    #[ORM\JoinColumn(nullable: true)]
    private ?PostEvent $events= null;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Transactions::class)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $transactions;

    #[ORM\OneToMany(mappedBy: 'member', targetEntity: Avantages::class)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $avantages;

    #[ORM\OneToOne(targetEntity: Customers::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customers $customer;

    #[ORM\OneToMany(mappedBy: 'member', targetEntity: Tballmessage::class)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $allmessages;

    #[ORM\OneToOne(targetEntity: Sectors::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Sectors $sector;

    #[ORM\ManyToOne(targetEntity: Gps::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Gps $locality= null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $datemaj_at;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active = true;

    #[ORM\ManyToMany(targetEntity: Activmember::class)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $memberlinks;

    #[ORM\ManyToMany(targetEntity: Taguery::class, inversedBy: 'members')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $tagueries;

    #[ORM\OneToMany(targetEntity: Notifmember::class, mappedBy: 'member')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $tbnotifs;

    #[ORM\OneToOne(targetEntity: TagAnalytic::class, inversedBy: 'member')]
    #[ORM\JoinColumn(nullable: true)]
    private ?TagAnalytic $analityc;

    public function __construct()
    {
        $this->create_at=new DateTime();
        $this->boardslist = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->allmessages = new ArrayCollection();
        $this->tagueries = new ArrayCollection();
        $this->tbnotifs = new ArrayCollection();
        $this->memberlinks = new ArrayCollection();
        $this->offre = new ArrayCollection();
        $this->avantages = new ArrayCollection();
        $this->gpreview = new ArrayCollection();

    }

    public function  __toString()
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPermission(): array
    {
        return $this->permission;
    }

    public function setPermission(array $permission): self
    {
        $this->permission = $permission;

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

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, Boardslist>
     */
    public function getBoardslist(): Collection
    {
        return $this->boardslist;
    }

    public function addBoardslist(Boardslist $boardslist): self
    {
        if (!$this->boardslist->contains($boardslist)) {
            $this->boardslist->add($boardslist);
            $boardslist->setMember($this);
        }

        return $this;
    }

    public function removeBoardslist(Boardslist $boardslist): self
    {
        if ($this->boardslist->removeElement($boardslist)) {
            // set the owning side to null (unless already changed)
            if ($boardslist->getMember() === $this) {
                $boardslist->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

        return $this;
    }

    public function getEvents(): ?PostEvent
    {
        return $this->events;
    }

    public function setEvents(?PostEvent $events): self
    {
        $this->events = $events;

        return $this;
    }

    /**
     * @return Collection<int, Transactions>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transactions $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setClient($this);
        }

        return $this;
    }

    public function removeTransaction(Transactions $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getClient() === $this) {
                $transaction->setClient(null);
            }
        }

        return $this;
    }

    public function getCustomer(): ?Customers
    {
        return $this->customer;
    }

    public function setCustomer(Customers $customer): self
    {
        $this->customer = $customer;

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

    public function getLocality(): ?Gps
    {
        return $this->locality;
    }

    public function setLocality(?Gps $locality): self
    {
        $this->locality = $locality;

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

    public function getAnalityc(): ?TagAnalytic
    {
        return $this->analityc;
    }

    public function setAnalityc(?TagAnalytic $analityc): self
    {
        $this->analityc = $analityc;

        return $this;
    }

    /**
     * @return Collection<int, Tballmessage>
     */
    public function getAllmessages(): Collection
    {
        return $this->allmessages;
    }

    public function addAllmessage(Tballmessage $allmessage): self
    {
        if (!$this->allmessages->contains($allmessage)) {
            $this->allmessages->add($allmessage);
            $allmessage->setMember($this);
        }

        return $this;
    }

    public function removeAllmessage(Tballmessage $allmessage): self
    {
        if ($this->allmessages->removeElement($allmessage)) {
            // set the owning side to null (unless already changed)
            if ($allmessage->getMember() === $this) {
                $allmessage->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notifmember>
     */
    public function getTbnotifs(): Collection
    {
        return $this->tbnotifs;
    }

    public function addTbnotif(Notifmember $tbnotif): self
    {
        if (!$this->tbnotifs->contains($tbnotif)) {
            $this->tbnotifs->add($tbnotif);
            $tbnotif->setMember($this);
        }

        return $this;
    }

    public function removeTbnotif(Notifmember $tbnotif): self
    {
        if ($this->tbnotifs->removeElement($tbnotif)) {
            // set the owning side to null (unless already changed)
            if ($tbnotif->getMember() === $this) {
                $tbnotif->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getMemberlinks(): Collection
    {
        return $this->memberlinks;
    }

    public function addDispatchlink(Activmember $memberlinks): self
    {
        if (!$this->memberlinks->contains($memberlinks)) {
            $this->memberlinks[] = $memberlinks;
        }

        return $this;
    }

    public function removeDispatchlink(Activmember $memberlinks): self
    {
        $this->memberlinks->removeElement($memberlinks);

        return $this;
    }

    public function addMemberlink(Activmember $memberlink): self
    {
        if (!$this->memberlinks->contains($memberlink)) {
            $this->memberlinks->add($memberlink);
        }

        return $this;
    }

    public function removeMemberlink(Activmember $memberlink): self
    {
        $this->memberlinks->removeElement($memberlink);

        return $this;
    }

    /**
     * @return Collection<int, Offres>
     */
    public function getOffre(): Collection
    {
        return $this->offre;
    }

    public function addOffre(Offres $offre): self
    {
        if (!$this->offre->contains($offre)) {
            $this->offre->add($offre);
            $offre->setAuthor($this);
        }

        return $this;
    }

    public function removeOffre(Offres $offre): self
    {
        if ($this->offre->removeElement($offre)) {
            // set the owning side to null (unless already changed)
            if ($offre->getAuthor() === $this) {
                $offre->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Avantages>
     */
    public function getAvantages(): Collection
    {
        return $this->avantages;
    }

    public function addAvantage(Avantages $avantage): self
    {
        if (!$this->avantages->contains($avantage)) {
            $this->avantages->add($avantage);
            $avantage->setMember($this);
        }

        return $this;
    }

    public function removeAvantage(Avantages $avantage): self
    {
        if ($this->avantages->removeElement($avantage)) {
            // set the owning side to null (unless already changed)
            if ($avantage->getMember() === $this) {
                $avantage->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, GpReview>
     */
    public function getGpreview(): Collection
    {
        return $this->gpreview;
    }

    public function addGpreview(GpReview $gpreview): static
    {
        if (!$this->gpreview->contains($gpreview)) {
            $this->gpreview->add($gpreview);
            $gpreview->setAuthor($this);
        }

        return $this;
    }

    public function removeGpreview(GpReview $gpreview): static
    {
        if ($this->gpreview->removeElement($gpreview)) {
            // set the owning side to null (unless already changed)
            if ($gpreview->getAuthor() === $this) {
                $gpreview->setAuthor(null);
            }
        }

        return $this;
    }

}
