<?php


namespace App\Entity\Comments;


use App\Entity\Member\Activmember;
use App\Entity\Users\Contacts;
use App\Repository\CommentNoticeRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: CommentNoticeRepository::class)]
#[ORM\Table(name:'aff_commentnotice')]
class CommentNotice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $isspaceweb=false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $isclient=false;

    #[ORM\ManyToOne(targetEntity: Activmember::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Activmember $spacewebexpe= null;

    #[ORM\ManyToOne(targetEntity: Contacts::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Contacts $contactexp= null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\OneToMany(mappedBy: 'comment', targetEntity: MsgsCommentNotice::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $msgs;

    public function __construct()
    {
        $this->create_at=new DateTime();
        $this->msgs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsspaceweb(): ?bool
    {
        return $this->isspaceweb;
    }

    public function setIsspaceweb(bool $isspaceweb): self
    {
        $this->isspaceweb = $isspaceweb;

        return $this;
    }

    public function getIsclient(): ?bool
    {
        return $this->isclient;
    }

    public function setIsclient(bool $isclient): self
    {
        $this->isclient = $isclient;

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

    public function getContactexp(): ?Contacts
    {
        return $this->contactexp;
    }

    public function setContactexp(?Contacts $contactexp): self
    {
        $this->contactexp = $contactexp;

        return $this;
    }

    public function isIsspaceweb(): ?bool
    {
        return $this->isspaceweb;
    }

    public function isIsclient(): ?bool
    {
        return $this->isclient;
    }

    public function getSpacewebexpe(): ?Activmember
    {
        return $this->spacewebexpe;
    }

    public function setSpacewebexpe(?Activmember $spacewebexpe): self
    {
        $this->spacewebexpe = $spacewebexpe;

        return $this;
    }

    /**
     * @return Collection<int, MsgsCommentNotice>
     */
    public function getMsgs(): Collection
    {
        return $this->msgs;
    }

    public function addMsg(MsgsCommentNotice $msg): self
    {
        if (!$this->msgs->contains($msg)) {
            $this->msgs->add($msg);
            $msg->setComment($this);
        }

        return $this;
    }

    public function removeMsg(MsgsCommentNotice $msg): self
    {
        if ($this->msgs->removeElement($msg)) {
            // set the owning side to null (unless already changed)
            if ($msg->getComment() === $this) {
                $msg->setComment(null);
            }
        }

        return $this;
    }

    public function isspaceweb(): ?bool
    {
        return $this->isspaceweb;
    }

    public function setSpaceweb(bool $isspaceweb): static
    {
        $this->isspaceweb = $isspaceweb;

        return $this;
    }

    public function isclient(): ?bool
    {
        return $this->isclient;
    }

    public function setClient(bool $isclient): static
    {
        $this->isclient = $isclient;

        return $this;
    }

}