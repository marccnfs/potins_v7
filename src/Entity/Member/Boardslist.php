<?php


namespace App\Entity\Member;

use App\Entity\Posts\Post;
use App\Entity\Boards\Board;
use App\Repository\BoardslistRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: BoardslistRepository::class)]
#[ORM\Table(name:"aff_boardlist")]
#[UniqueEntity(fields: ['token'],)]
class Boardslist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Activmember::class, inversedBy: 'boardslist')]
    private ?Activmember $member= null;

    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'members')]
    private Collection $postmember;

    #[ORM\Column(type: Types::JSON)]
    private ?array $termes = [];

    #[ORM\Column(nullable: true)]
    private ?string $token;

    #[ORM\Column(length: 50,nullable: false)]
    private ?string $role = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isadmin = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isdefault = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $datemaj_at;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $active = true;

    #[ORM\ManyToOne(targetEntity: Board::class, cascade: ['persist','remove'], inversedBy: 'boardslist')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Board $board = null;

    public function __construct()
    {
        $this->create_at=new DateTime();
        $this->postmember = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTermes(): array
    {
        return $this->termes;
    }

    public function setTermes(array $termes): self
    {
        $this->termes = $termes;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function isSuper(): ?string
    {
        return $this->role==="superadmin";
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function isIsadmin(): ?bool
    {
        return $this->isadmin;
    }

    public function setIsadmin(bool $isadmin): self
    {
        $this->isadmin = $isadmin;

        return $this;
    }
    public function getIsadmin(): ?bool
    {
        return $this->isadmin;
    }

    public function isIsdefault(): ?bool
    {
        return $this->isdefault;
    }

    public function setIsdefault(bool $isdefault): self
    {
        $this->isdefault = $isdefault;

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

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getMember(): ?Activmember
    {
        return $this->member;
    }

    public function setMember(?Activmember $member): self
    {
        $this->member = $member;

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPostmember(): Collection
    {
        return $this->postmember;
    }

    public function addPostmember(Post $postmember): self
    {
        if (!$this->postmember->contains($postmember)) {
            $this->postmember->add($postmember);
            $postmember->addMember($this);
        }

        return $this;
    }

    public function removePostmember(Post $postmember): self
    {
        if ($this->postmember->removeElement($postmember)) {
            $postmember->removeMember($this);
        }

        return $this;
    }

    public function getBoard(): ?Board
    {
        return $this->board;
    }

    public function setBoard(?Board $board): self
    {
        $this->board = $board;

        return $this;
    }
    public function activeAdmin(): self
    {
        $this->isadmin = true;

        return $this;
    }

    public function isadmin(): ?bool
    {
        return $this->isadmin;
    }

    public function setAdmin(bool $isadmin): static
    {
        $this->isadmin = $isadmin;

        return $this;
    }

    public function isdefault(): ?bool
    {
        return $this->isdefault;
    }

    public function setDefault(bool $isdefault): static
    {
        $this->isdefault = $isdefault;

        return $this;
    }

}
