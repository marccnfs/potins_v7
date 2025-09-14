<?php


namespace App\Entity\LogMessages;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name:"aff_loginner")]
class Loginner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(length: 100,nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(nullable: true)]
    private ?string $uri = null;

    #[ORM\Column(nullable: true)]
    private ?string $referer = null;

    #[ORM\Column(length: 50,nullable: true)]
    private ?string $agent = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\OneToOne(inversedBy: 'msglog', targetEntity: Msgs::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Msgs $msg;


    public function __construct()
    {
        $this->create_at = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(?string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function setReferer(?string $referer): self
    {
        $this->referer = $referer;

        return $this;
    }

    public function getAgent(): ?string
    {
        return $this->agent;
    }

    public function setAgent(?string $agent): self
    {
        $this->agent = $agent;

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

    public function getMsg(): ?Msgs
    {
        return $this->msg;
    }

    public function setMsg(?Msgs $msg): self
    {
        $this->msg = $msg;

        // set (or unset) the owning side of the relation if necessary
        $newMsglog = null === $msg ? null : $this;
        if ($msg->getMsglog() !== $newMsglog) {
            $msg->setMsglog($newMsglog);
        }

        return $this;
    }

}