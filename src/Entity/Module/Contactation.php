<?php


namespace App\Entity\Module;

use App\Entity\Boards\Board;
use App\Repository\ContactationRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ContactationRepository::class)]
#[ORM\Table(name:"aff_contactation")]
class Contactation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Board::class, mappedBy: 'contactation')]
    private ?Board $board = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $active = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $deleted = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $datemaj_at;

    #[ORM\Column(nullable: true)]
    private ?string $linkone;

    #[ORM\Column(nullable: true)]
    private ?string $keycontactation;

    #[ORM\Column(nullable: true)]
    private ?string $keymodule;

    public function __construct()
    {
        $this->create_at = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFilehtml():string
    {
        $directory= __DIR__ . './../../public/htmlCntPvd/' . $this->key . 'html';
        $file=file_get_contents($directory);
        return $file;
    }

    public function getLinkone(): ?string
    {
        return $this->linkone;
    }

    public function setLinkone(?string $linkone): self
    {
        $this->linkone = $linkone;

        return $this;
    }

    public function getKeycontactation(): ?string
    {
        return $this->keycontactation;
    }

    public function setKeycontactation(?string $keycontactation): self
    {
        $this->keycontactation = $keycontactation;

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

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getKeymodule(): ?string
    {
        return $this->keymodule;
    }

    public function setKeymodule(?string $keymodule): self
    {
        $this->keymodule = $keymodule;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
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

}
