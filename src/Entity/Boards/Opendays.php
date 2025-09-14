<?php


namespace App\Entity\Boards;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTime;



#[ORM\Entity]
#[ORM\Table(name: 'aff_tabopen')]
class Opendays
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private ?string $tabunique;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private ?string $conges;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $tabuniquejso;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $congesjso;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $create_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $datemaj_at;

    public function __construct()
    {
        $this->create_at=new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTabunique(): ?string
    {
        return $this->tabunique;
    }

    public function setTabunique(string $tabunique): self
    {
        $this->tabunique = $tabunique;

        return $this;
    }

    public function getConges(): ?string
    {
        return $this->conges;
    }

    public function setConges(?string $conges): self
    {
        $this->conges = $conges;

        return $this;
    }

    public function getTabuniquejso(): ?array
    {
        return $this->tabuniquejso;
    }

    public function setTabuniquejso(?array $tabuniquejso): self
    {
        $this->tabuniquejso = $tabuniquejso;

        return $this;
    }

    public function getCongesjso(): ?array
    {
        return $this->congesjso;
    }

    public function setCongesjso(?array $congesjso): self
    {
        $this->congesjso = $congesjso;

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

}