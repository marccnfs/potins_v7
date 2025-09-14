<?php


namespace App\Entity\UserMap;


use App\Entity\Marketplace\Noticeproducts;
use App\Entity\Member\Activmember;
use App\Entity\Module\PostEvent;
use App\Entity\Posts\Post;
use App\Entity\Boards\Template;
use App\Repository\TagueryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: TagueryRepository::class)]
#[ORM\Table(name:"aff_taguery")]
#[UniqueEntity(fields: ['namewebsite'])]
class Taguery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: PostEvent::class, mappedBy: 'tagueries')]
    private Collection $postevents;

    #[ORM\ManyToMany(targetEntity: Activmember::class, mappedBy: 'tagueries')]
    private Collection $members;

    #[ORM\ManyToMany(targetEntity: Template::class, mappedBy: 'tagueries')]
    private Collection $template;

    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'tagueries')]
    private Collection $postations;

    #[ORM\ManyToMany(targetEntity: Noticeproducts::class, mappedBy: 'tagueries')]
    private Collection $noticeproducts;

    #[ORM\ManyToMany(targetEntity: Tagcat::class, mappedBy: 'tagueries')]
    private Collection $catag;

    #[ORM\Column(nullable: false)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $associatekey = null;

    #[ORM\Column(length: 125, nullable: true)]
    private ?string $phylo= null;

    public function __construct()
    {
        $this->postevents = new ArrayCollection();
        $this->postations = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->template = new ArrayCollection();
        $this->catag = new ArrayCollection();
        $this->noticeproducts = new ArrayCollection();
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

    public function getAssociatekey(): ?string
    {
        return $this->associatekey;
    }

    public function setAssociatekey(?string $associatekey): self
    {
        $this->associatekey = $associatekey;

        return $this;
    }

    public function getPhylo(): ?string
    {
        return $this->phylo;
    }

    public function setPhylo(string $phylo): self
    {
        $this->phylo = $phylo;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getPostevents(): Collection
    {
        return $this->postevents;
    }

    public function addPostevent(PostEvent $postevent): self
    {
        if (!$this->postevents->contains($postevent)) {
            $this->postevents[] = $postevent;
            $postevent->addTaguery($this);
        }

        return $this;
    }

    public function removePostevent(PostEvent $postevent): self
    {
        if ($this->postevents->contains($postevent)) {
            $this->postevents->removeElement($postevent);
            $postevent->removeTaguery($this);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getPostations(): Collection
    {
        return $this->postations;
    }

    public function addPostation(Post $postation): self
    {
        if (!$this->postations->contains($postation)) {
            $this->postations[] = $postation;
            $postation->addTaguery($this);
        }

        return $this;
    }

    public function removePostation(Post $postation): self
    {
        if ($this->postations->contains($postation)) {
            $this->postations->removeElement($postation);
            $postation->removeTaguery($this);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getCatag(): Collection
    {
        return $this->catag;
    }

    public function addCatag(Tagcat $catag): self
    {
        if (!$this->catag->contains($catag)) {
            $this->catag[] = $catag;
            $catag->addTaguery($this);
        }

        return $this;
    }

    public function removeCatag(Tagcat $catag): self
    {
        if ($this->catag->removeElement($catag)) {
            $catag->removeTaguery($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Activmember>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Activmember $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->addTaguery($this);
        }

        return $this;
    }

    public function removeMember(Activmember $member): self
    {
        if ($this->members->removeElement($member)) {
            $member->removeTaguery($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Template>
     */
    public function getTemplate(): Collection
    {
        return $this->template;
    }

    public function addTemplate(Template $template): self
    {
        if (!$this->template->contains($template)) {
            $this->template->add($template);
            $template->addTaguery($this);
        }

        return $this;
    }

    public function removeTemplate(Template $template): self
    {
        if ($this->template->removeElement($template)) {
            $template->removeTaguery($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Noticeproducts>
     */
    public function getNoticeproducts(): Collection
    {
        return $this->noticeproducts;
    }

    public function addNoticeproduct(Noticeproducts $noticeproduct): self
    {
        if (!$this->noticeproducts->contains($noticeproduct)) {
            $this->noticeproducts->add($noticeproduct);
            $noticeproduct->addTaguery($this);
        }

        return $this;
    }

    public function removeNoticeproduct(Noticeproducts $noticeproduct): self
    {
        if ($this->noticeproducts->removeElement($noticeproduct)) {
            $noticeproduct->removeTaguery($this);
        }

        return $this;
    }
}
