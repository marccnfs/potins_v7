<?php


namespace App\Entity\Customer;

use App\Entity\Member\Activmember;
use App\Entity\LogMessages\PrivateConvers;
use App\Entity\Marketplace\Offres;
use App\Repository\TransactionsRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;



#[ORM\Entity(repositoryClass: TransactionsRepository::class)]
#[ORM\Table(name:"aff_transactions")]
class Transactions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Offres::class,inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Offres $offre= null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\OneToOne(targetEntity: PrivateConvers::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?PrivateConvers $convers;

    #[ORM\ManyToOne(targetEntity: Activmember::class,inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Activmember $client= null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $endAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $modifAt;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $active = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $closed = false;

    #[ORM\Column(nullable: true)]
    private ?string $motifclosed = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function __construct()
    {
        $this->create_at=new DateTime();
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

    public function getClosed(): ?bool
    {
        return $this->closed;
    }

    public function setClosed(bool $closed): self
    {
        $this->closed = $closed;

        return $this;
    }

    public function getMotifclosed(): ?string
    {
        return $this->motifclosed;
    }

    public function setMotifclosed(string $motifclosed): self
    {
        $this->motifclosed = $motifclosed;

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

    public function getConvers(): ?PrivateConvers
    {
        return $this->convers;
    }

    public function setConvers(?PrivateConvers $convers): self
    {
        $this->convers = $convers;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function isClosed(): ?bool
    {
        return $this->closed;
    }

    public function getClient(): ?Activmember
    {
        return $this->client;
    }

    public function setClient(?Activmember $client): self
    {
        $this->client = $client;

        return $this;
    }
}