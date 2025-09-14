<?php

namespace App\Entity\Games;

use App\Repository\PuzzleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PuzzleRepository::class)]
#[ORM\Table(name:"aff_puzzle")]
class Puzzle
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;
    #[ORM\ManyToOne(inversedBy:"puzzles")]
    private EscapeGame $escapeGame;
    #[ORM\Column] private int $step;               // 1..6
    #[ORM\Column(length:50)]
    private string $type; // "cryptex", "lien_externe", "qrcode", etc.

    // Stocke la config spécifique (solution, alphabet, messages...) en JSON
    #[ORM\Column(type:"json")]
    private array $config = [];

    #[ORM\Column(length:120)]
    private string $title;

    #[ORM\Column(type:"text", nullable:true)]
    private ?string $prompt = null; // consignes à l’utilisateur-joueur

    // Pour les énigmes externes (LearningApps, etc.)
    #[ORM\Column(length:255, nullable:true)]
    private ?string $externalUrl = null;

    // Optionnel : statut de complétude de l’étape
    #[ORM\Column(options:["default"=>false])]
    private bool $ready = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStep(): ?int
    {
        return $this->step;
    }

    public function setStep(int $step): static
    {
        $this->step = $step;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getPrompt(): ?string
    {
        return $this->prompt;
    }

    public function setPrompt(?string $prompt): static
    {
        $this->prompt = $prompt;

        return $this;
    }

    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    public function setExternalUrl(?string $externalUrl): static
    {
        $this->externalUrl = $externalUrl;

        return $this;
    }

    public function isReady(): ?bool
    {
        return $this->ready;
    }

    public function setReady(bool $ready): static
    {
        $this->ready = $ready;

        return $this;
    }

    public function getEscapeGame(): ?EscapeGame
    {
        return $this->escapeGame;
    }

    public function setEscapeGame(?EscapeGame $escapeGame): static
    {
        $this->escapeGame = $escapeGame;

        return $this;
    }
}
