<?php

namespace App\Entity\Agenda;


use App\Entity\Admin\WbOrderProducts;
use App\Entity\Module\PostEvent;
use App\Repository\SubscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;


#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
#[ORM\Table(name: 'aff_subscription')]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'type_subscription', type: Types::INTEGER)]
    private ?int $typeSubscription;


    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $starttime;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $endtime;

    #[ORM\OneToOne(targetEntity: WbOrderProducts::class, mappedBy: 'subscription',fetch: 'LAZY')]
    private ?WbOrderProducts $wbprodorder = null;

    #[ORM\ManyToOne(targetEntity: PostEvent::class)]
    #[JoinColumn(nullable: true)]
    private ?PostEvent $event;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[JoinColumn(nullable: false)]
    private ?bool $closed=false;

    public function __construct()
    {
        $this->typeSubscription = 1;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeSubscription(): ?int
    {
        return $this->typeSubscription;
    }

    public function setTypeSubscription(int $typeSubscription): self
    {
        $this->typeSubscription = $typeSubscription;
        return $this;
    }

    public function getStarttime(): ?\DateTime
    {
        return $this->starttime;
    }

    public function setStarttime(?\DateTime $starttime): self
    {
        $this->starttime = $starttime;

        return $this;
    }

    public function getEndtime(): ?\DateTime
    {
        return $this->endtime;
    }

    public function setEndtime(?\DateTime $endtime): self
    {
        $this->endtime = $endtime;

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
    public function getWbprodorder(): ?WbOrderProducts
    {
        return $this->wbprodorder;
    }

    public function setWbprodorder(?WbOrderProducts $wbprodorder): self
    {
        $this->wbprodorder = $wbprodorder;

        return $this;
    }

    public function isClosed(): ?bool
    {
        return $this->closed;
    }

    public function getEvent(): ?PostEvent
    {
        return $this->event;
    }

    public function setEvent(?PostEvent $event): self
    {
        $this->event = $event;

        return $this;
    }
}
