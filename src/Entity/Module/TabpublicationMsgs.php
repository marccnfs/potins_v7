<?php


namespace App\Entity\Module;


use App\Entity\LogMessages\PublicationConvers;
use App\Entity\Marketplace\Offres;
use App\Entity\Posts\Post;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name:"aff_tabpublicationMsgs")]
class TabpublicationMsgs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: PublicationConvers::class, mappedBy: 'tabpublication')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $idmessage;

    #[ORM\OneToOne(targetEntity: Post::class, mappedBy: 'tbmessages', cascade: ['persist', 'remove'])]
    private ?Post $post = null;

    #[ORM\OneToOne(targetEntity: Offres::class, mappedBy: 'tbmessages', cascade: ['persist', 'remove'])]
    private ?Offres $offre = null;


    public function __construct()
    {
        $this->idmessage = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function getOffre(): ?Offres
    {
        return $this->offre;
    }

    public function setOffre(?Offres $offre): self
    {
        $this->offre = $offre;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getIdmessage(): Collection
    {
        return $this->idmessage;
    }

    public function addIdmessage(PublicationConvers $idmessage): self
    {
        if (!$this->idmessage->contains($idmessage)) {
            $this->idmessage[] = $idmessage;
            $idmessage->setTabpublication($this);
        }

        return $this;
    }

    public function removeIdmessage(PublicationConvers $idmessage): self
    {
        if ($this->idmessage->removeElement($idmessage)) {
            // set the owning side to null (unless already changed)
            if ($idmessage->getTabpublication() === $this) {
                $idmessage->setTabpublication(null);
            }
        }

        return $this;
    }

}
