<?php

namespace App\Entity\Boards;

use App\Entity\Admin\Wbcustomers;
use App\Entity\LogMessages\MsgBoard;
use App\Entity\Member\Boardslist;
use App\Entity\Module\Contactation;
use App\Entity\Module\ModuleList;
use App\Entity\Module\PostEvent;
use App\Entity\Sector\Gps;
use App\Entity\UserMap\Hits;
use App\Repository\BoardRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\String\Slugger\SluggerInterface;


#[ORM\Entity(repositoryClass: BoardRepository::class)]
#[ORM\Table(name: 'aff_board')]
#[UniqueEntity(
    fields: ['codesite','nameboard','slug'],
    message: 'Ce nom est délà utlisé...',
    errorPath: 'nameboard',
)]
class Board
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 100, unique: true, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: 'le nom doit faire au moins {{ limit }} caractères',
        maxMessage: 'le nom doit faire au maximum {{ limit }} caractères',)]
    private ?string $nameboard;

    #[ORM\Column(nullable: true)]
    #[Assert\Url(
        message: "l'url '{{ value }}' n'est pas valide",)]
    private ?string $url;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $statut=false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $locatemedia=false;

    #[ORM\Column(nullable: true)]
    private ?string $codesite;

    #[OneToOne(targetEntity: Wbcustomers::class, cascade: ['persist'])]
    #[JoinColumn(nullable: true)]
    private ?Wbcustomers $wbcustomer;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $create_at;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $datemaj_at;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $active = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $attached = true;

    #[OneToOne(targetEntity: Opendays::class, cascade: ['persist','remove'])]
    #[JoinColumn(nullable: true)]
    private ?Opendays $tabopendays;

    #[OneToOne(targetEntity: Template::class, cascade: ['persist','remove'])]
    #[JoinColumn(nullable: true)]
    private ?Template $template;

    #[OneToMany(targetEntity: ModuleList::class, mappedBy: 'board', cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $listmodules;

    #[OneToOne(targetEntity: Contactation::class, inversedBy: 'board', cascade: ['persist','remove'])]
    #[JoinColumn(nullable: true)]
    private ?Contactation $contactation;

    #[OneToMany(targetEntity: Boardslist::class, mappedBy: 'board')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $boardslist;

    #[ORM\ManyToOne(targetEntity: Gps::class, inversedBy: 'boards')]
    #[ORM\JoinColumn(nullable: true)]
    private Gps $locality;

    #[ORM\ManyToOne(targetEntity: LinksBoards::class, inversedBy: 'boards')]
    #[ORM\JoinColumn(nullable: true)]
    private LinksBoards $links;

    #[ORM\Column(unique: true)]
    private ?string $slug;

    #[OneToMany(targetEntity: MsgBoard::class, mappedBy: 'boarddest', cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $msgs;

    #[OneToOne(targetEntity: Hits::class, inversedBy: 'board')]
    #[JoinColumn(nullable: true)]
    private ?Hits $hits;

    #[ORM\OneToMany(targetEntity: PostEvent::class, mappedBy: 'locatemedia')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $events;

    public function __construct()
    {
        $this->create_at=new DateTime();
        $this->listmodules = new ArrayCollection();
        $this->boardslist = new ArrayCollection();
        $this->msgs = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function  __toString(): string
    {
        return $this->nameboard;
    }

    public function boardSlug(SluggerInterface $slugger)
    {
        $this->slug = (string) $slugger->slug((string) $this)->lower();
        /*
        if (!$this->slug || '-' === $this->slug) {
            $this->slug = (string) $slugger->slug((string) $this)->lower();
        }
        */
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getCodesite(): ?string
    {
        return $this->codesite;
    }

    public function setCodesite(?string $codesite): self
    {
        $this->codesite = $codesite;

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

    public function getAttached(): ?bool
    {
        return $this->attached;
    }

    public function setAttached(bool $attached): self
    {
        $this->attached = $attached;

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

    public function getTabopendays(): ?Opendays
    {
        return $this->tabopendays;
    }

    public function setTabopendays(?Opendays $tabopendays): self
    {
        $this->tabopendays = $tabopendays;

        return $this;
    }

    public function getTemplate(): ?Template
    {
        return $this->template;
    }

    public function setTemplate(?Template $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getNameboard(): ?string
    {
        return $this->nameboard;
    }

    public function setNameboard(string $nameboard): self
    {
        $this->nameboard = $nameboard;

        return $this;
    }

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function isAttached(): ?bool
    {
        return $this->attached;
    }

    public function getWbcustomer(): ?Wbcustomers
    {
        return $this->wbcustomer;
    }

    public function setWbcustomer(?Wbcustomers $wbcustomer): self
    {
        $this->wbcustomer = $wbcustomer;

        return $this;
    }

    /**
     * @return Collection<int, ModuleList>
     */
    public function getListmodules(): Collection
    {
        return $this->listmodules;
    }

    public function addListmodule(ModuleList $listmodule): self
    {
        if (!$this->listmodules->contains($listmodule)) {
            $this->listmodules->add($listmodule);
            $listmodule->setBoard($this);
        }

        return $this;
    }

    public function removeListmodule(ModuleList $listmodule): self
    {
        if ($this->listmodules->removeElement($listmodule)) {
            // set the owning side to null (unless already changed)
            if ($listmodule->getBoard() === $this) {
                $listmodule->setBoard(null);
            }
        }

        return $this;
    }

    public function getContactation(): ?Contactation
    {
        return $this->contactation;
    }

    public function setContactation(?Contactation $contactation): self
    {
        $this->contactation = $contactation;

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
            $boardslist->setBoard($this);
        }

        return $this;
    }

    public function removeBoardslist(Boardslist $boardslist): self
    {
        if ($this->boardslist->removeElement($boardslist)) {
            // set the owning side to null (unless already changed)
            if ($boardslist->getBoard() === $this) {
                $boardslist->setBoard(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MsgBoard>
     */
    public function getMsgs(): Collection
    {
        return $this->msgs;
    }

    public function addMsg(MsgBoard $msg): self
    {
        if (!$this->msgs->contains($msg)) {
            $this->msgs->add($msg);
            $msg->setBoarddest($this);
        }

        return $this;
    }

    public function removeMsg(MsgBoard $msg): self
    {
        if ($this->msgs->removeElement($msg)) {
            // set the owning side to null (unless already changed)
            if ($msg->getBoarddest() === $this) {
                $msg->setBoarddest(null);
            }
        }

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

    public function isLocatemedia(): ?bool
    {
        return $this->locatemedia;
    }

    public function setLocatemedia(bool $locatemedia): self
    {
        $this->locatemedia = $locatemedia;

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

    public function getLinks(): ?LinksBoards
    {
        return $this->links;
    }

    public function setLinks(?LinksBoards $links): self
    {
        $this->links = $links;

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
            $event->setLocatemedia($this);
        }

        return $this;
    }

    public function removeEvent(PostEvent $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getLocatemedia() === $this) {
                $event->setLocatemedia(null);
            }
        }

        return $this;
    }
}
