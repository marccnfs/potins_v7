<?php


namespace App\Entity\Agenda;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity]
#[ORM\Table(name: 'aff_tabdate')]
class Tabdate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[JoinColumn(nullable: false)]
    private ?string $tabdatestr;

    #[Groups(['edit_event'])]
    #[ORM\Column(type: Types::JSON, nullable: false)]
    private ?array $tabdatejso;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTabdatestr(): ?string
    {
        return $this->tabdatestr;
    }

    public function setTabdatestr(string $tabdatestr): self
    {
        $this->tabdatestr = $tabdatestr;

        return $this;
    }

    public function getTabdatejso(): ?array
    {
        return $this->tabdatejso;
    }

    public function setTabdatejso(?array $tabdatejso): self
    {
        $this->tabdatejso = $tabdatejso;

        return $this;
    }


}