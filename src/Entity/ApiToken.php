<?php

namespace App\Entity;

use App\Repository\ApiTokenRepository;
use DateTime;
use DateTimeInterface;
use App\Entity\Users\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ApiTokenRepository::class)]
#[ORM\Table(name:'aff_apptoken')]
class ApiToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $token = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $expireAt;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user;

    public function __construct(User $user)
    {
        $this->token=bin2hex(random_bytes(60));
        $this->user=$user;
        $this->expireAt=new DateTime('+1 hour');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getExpireAt(): ?DateTime
    {
        return $this->expireAt;
    }

    public function setExpireAt(DateTime $expireAt): self
    {
        $this->expireAt = $expireAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function renewExpireAt()
    {
        $this->expireAt=new DateTime('+1 hour');
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

}
