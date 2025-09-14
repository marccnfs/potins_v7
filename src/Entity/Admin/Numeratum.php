<?php


namespace App\Entity\Admin;

use App\Repository\NumeratumRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: NumeratumRepository::class)]
#[ORM\Table(name:"aff_numeratum")]
#[UniqueEntity(fields: ['numFact','numCmd','numClient'])]
class Numeratum
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $numCmd;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $numFact;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $numClient;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $numWebsite=null;

    public function __construct($init)
    {
        $this->numCmd=$init;
        $this->numFact=$init;
        $this->numClient=$init;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumCmd(): ?int
    {
        return $this->numCmd;
    }

    public function setNumCmd(int $numCmd): self
    {
        $this->numCmd = $numCmd;

        return $this;
    }

    public function getNumFact(): ?int
    {
        return $this->numFact;
    }

    public function setNumFact(int $numFact): self
    {
        $this->numFact = $numFact;

        return $this;
    }

    public function getNumClient(): ?int
    {
        return $this->numClient;
    }

    public function setNumClient(int $numClient): self
    {
        $this->numClient = $numClient;

        return $this;
    }

    public function getNumWebsite(): ?int
    {
        return $this->numWebsite;
    }

    public function setNumWebsite(?int $numWebsite): self
    {
        $this->numWebsite = $numWebsite;

        return $this;
    }
}