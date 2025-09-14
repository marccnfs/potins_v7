<?php


namespace App\Entity\Bulles;

use App\Entity\Member\Activmember;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name:'aff_bullette')]
class Bullette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Bulle::class, inversedBy: 'bullettes')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Bulle $bulle= null;

    #[ORM\ManyToOne(targetEntity: Activmember::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Activmember $spacewebanswser= null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['buble'])]
    private ?string $contentHtml = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['buble'])]
    private ?string $bodyTxt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $expire_at;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private bool $ownerOfgroup=false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private bool $warning=false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private bool $deleted=false;

    public function __construct()
    {
        $this->create_at=new DateTime();
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

    public function getExpireAt(): ?\DateTime
    {
        return $this->expire_at;
    }

    public function setExpireAt(?\DateTime $expire_at): self
    {
        $this->expire_at = $expire_at;

        return $this;
    }

    public function getWarning(): ?bool
    {
        return $this->warning;
    }

    public function setWarning(?bool $warning): self
    {
        $this->warning = $warning;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getBulle(): ?Bulle
    {
        return $this->bulle;
    }

    public function setBulle(?Bulle $bulle): self
    {
        $this->bulle = $bulle;

        return $this;
    }

    public function getOwnerOfgroup(): ?bool
    {
        return $this->ownerOfgroup;
    }

    public function setOwnerOfgroup(?bool $ownerOfgroup): self
    {
        $this->ownerOfgroup = $ownerOfgroup;

        return $this;
    }

    public function getSpacewebanswser(): ?Activmember
    {
        return $this->spacewebanswser;
    }

    public function setSpacewebanswser(?Activmember $spacewebanswser): self
    {
        $this->spacewebanswser = $spacewebanswser;

        return $this;
    }

    public function isOwnerOfgroup(): ?bool
    {
        return $this->ownerOfgroup;
    }

    public function isWarning(): ?bool
    {
        return $this->warning;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }
}