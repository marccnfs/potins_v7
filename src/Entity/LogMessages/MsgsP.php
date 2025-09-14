<?php


namespace App\Entity\LogMessages;

use App\Entity\Member\Activmember;
use App\Entity\Users\Contacts;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
#[ORM\Table(name:"aff_msgspublication")]
class MsgsP
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Activmember::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Activmember $authormember= null;

    #[ORM\ManyToOne(targetEntity: Contacts::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Contacts $authorcontact= null;

    #[ORM\ManyToOne(targetEntity: PublicationConvers::class,inversedBy: 'msgs')]
    #[ORM\JoinColumn(nullable: true)]
    private ?PublicationConvers $publicationmsg= null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contentHtml = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(min: 5)]
    private ?string $bodyTxt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\OneToMany(targetEntity: TbmsgP::class, mappedBy: 'idmessage', cascade: ['persist', 'remove'])]
    private Collection $tabreaders;

    #[ORM\OneToOne(targetEntity: Loginner::class, mappedBy: 'msg', cascade: ['persist', 'remove'])]
    private ?Loginner $msglog = null;

    public function __construct()
    {
        $this->create_at = new DateTime();
        $this->tabreaders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContentHtml(): ?string
    {
        return $this->contentHtml;
    }

    public function setContentHtml(?string $contentHtml): self
    {
        $this->contentHtml = $contentHtml;

        return $this;
    }

    public function getBodyTxt(): ?string
    {
        return $this->bodyTxt;
    }

    public function setBodyTxt(?string $bodyTxt): self
    {
        $this->bodyTxt = $bodyTxt;

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
    public function getAuthorcontact(): ?Contacts
    {
        return $this->authorcontact;
    }

    public function setAuthorcontact(?Contacts $authorcontact): self
    {
        $this->authorcontact = $authorcontact;

        return $this;
    }

    public function getPublicationmsg(): ?PublicationConvers
    {
        return $this->publicationmsg;
    }

    public function setPublicationmsg(?PublicationConvers $publicationmsg): self
    {
        $this->publicationmsg = $publicationmsg;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getTabreaders(): Collection
    {
        return $this->tabreaders;
    }

    public function addTabreader(TbmsgP $tabreader): self
    {
        if (!$this->tabreaders->contains($tabreader)) {
            $this->tabreaders[] = $tabreader;
            $tabreader->setIdmessage($this);
        }

        return $this;
    }

    public function removeTabreader(TbmsgP $tabreader): self
    {
        if ($this->tabreaders->removeElement($tabreader)) {
            // set the owning side to null (unless already changed)
            if ($tabreader->getIdmessage() === $this) {
                $tabreader->setIdmessage(null);
            }
        }

        return $this;
    }

    public function getMsglog(): ?Loginner
    {
        return $this->msglog;
    }

    public function setMsglog(?Loginner $msglog): self
    {
        $this->msglog = $msglog;

        return $this;
    }

    public function getAuthormember(): ?Activmember
    {
        return $this->authormember;
    }

    public function setAuthormember(?Activmember $authormember): self
    {
        $this->authormember = $authormember;

        return $this;
    }

}
