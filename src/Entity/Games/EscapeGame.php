<?php

namespace App\Entity\Games;

use App\Entity\Media\Illustration;
use App\Entity\Users\Participant;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\EscapeGameRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity(repositoryClass: EscapeGameRepository::class)]
#[ORM\Table(name:"aff_escapegame")]
class EscapeGame
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?Participant $owner = null;// sécurité

    #[ORM\Column(length:120)]
    private string $title;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $progression = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $motsClesTrouves = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $enigmes = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $universe = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $titresEtapes = [];

    #[ORM\Column(type: 'boolean',nullable: true, options:["default"=>false])]
    private bool $published = false;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $difficulty = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $durationMinutes = null;

    #[ORM\Column(length: 120, unique: true, nullable: true)]
    private ?string $shareSlug = null; // pour l'URL publique

    #[ORM\OneToMany(targetEntity: Puzzle::class, mappedBy: "escapeGame", cascade: ["persist","remove"], orphanRemoval: true)]
    #[ORM\OrderBy(["step" => "ASC"])]
    private Collection $puzzles;

    #[ORM\OneToMany(targetEntity: PlaySession::class, mappedBy: 'escapeGame', cascade: ['remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $sessions;

    #[ORM\ManyToOne(targetEntity: Participant::class, inversedBy: "escapeGames")]
    //#[ORM\OrderBy(["step" => "ASC"])]
    private ?Participant $participant = null;

    #[ORM\OneToMany(targetEntity: Illustration::class, mappedBy: 'escapeGame', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $illustrations;

    #[ORM\OneToMany(targetEntity: MobileLink::class, mappedBy: 'escapeGame', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $mobilelink;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $created_at;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $datemaj_at;

    public function __construct()
    {
        $this->puzzles = new ArrayCollection();
        $this->illustrations = new ArrayCollection();
        $this->created_at=new DateTime();
        $this->sessions = new ArrayCollection();
        $this->mobilelink = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUniverse(): array
    {
        return $this->universe ?? [];
    }

    public function setUniverse(array $universe): static
    {
        $this->universe = $universe;

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): static
    {
        $this->published = $published;

        return $this;
    }

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(?string $difficulty): static
    {
        $this->difficulty = $difficulty !== '' ? $difficulty : null;

        return $this;
    }

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(?int $durationMinutes): static
    {
        if ($durationMinutes !== null) {
            $durationMinutes = max(0, (int) $durationMinutes);
        }

        $this->durationMinutes = $durationMinutes ?: null;

        return $this;
    }

    public function getDurationCategory(): ?string
    {
        if ($this->durationMinutes === null) {
            return null;
        }

        if ($this->durationMinutes <= 15) {
            return 'short';
        }

        if ($this->durationMinutes <= 30) {
            return 'medium';
        }

        return 'long';
    }

    public function getShareSlug(): ?string
    {
        return $this->shareSlug;
    }

    public function setShareSlug(string $shareSlug): static
    {
        $this->shareSlug = $shareSlug;

        return $this;
    }

    public function getOwner(): ?Participant
    {
        return $this->owner;
    }

    public function setOwner(?Participant $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, Puzzle>
     */
    public function getPuzzles(): Collection
    {
        return $this->puzzles;
    }

    public function getPuzzleByStep(int $step): ?Puzzle {
        foreach ($this->puzzles as $p) if ($p->getStep() === $step) return $p;
        return null;
    }

    public function getOrCreatePuzzleByStep(int $step, string $type): Puzzle {
        $p = $this->getPuzzleByStep($step);
        if ($p) return $p;
        $p = (new Puzzle())->setEscapeGame($this)->setStep($step)->setType($type)->setReady(false);
        $this->puzzles->add($p);
        return $p;
    }

    /** true si toutes les étapes 1..6 sont prêtes */
    public function isComplete(): bool {
        $found = [];
        foreach ($this->puzzles as $p) $found[$p->getStep()] = $p->isReady();
        for ($i=1; $i<=6; $i++) if (empty($found[$i])) return false;
        return true;
    }

    /** Prochaine étape non prête (ou null si tout OK) */
    public function nextIncompleteStep(): ?int {
        $ready = [];
        foreach ($this->puzzles as $p) $ready[$p->getStep()] = $p->isReady();
        for ($i=1; $i<=6; $i++) if (empty($ready[$i])) return $i;
        return null;
    }

    /** Génère un slug partage si absent */
    public function ensureShareSlug(callable $slugger): void {
        if ($this->shareSlug) return;
        // $slugger: fn(string $seed): string
        $this->shareSlug = $slugger(($this->getTitle() ?: 'eg').'-'.bin2hex(random_bytes(4)));
    }

    public function addPuzzle(Puzzle $puzzle): static
    {
        if (!$this->puzzles->contains($puzzle)) {
            $this->puzzles->add($puzzle);
            $puzzle->setEscapeGame($this);
        }

        return $this;
    }

    public function removePuzzle(Puzzle $puzzle): static
    {
        if ($this->puzzles->removeElement($puzzle)) {
            // set the owning side to null (unless already changed)
            if ($puzzle->getEscapeGame() === $this) {
                $puzzle->setEscapeGame(null);
            }
        }

        return $this;
    }
    public function getProgression(): ?array
    {
        return $this->progression;
    }

    public function setProgression(?array $progression): static
    {
        $this->progression = $progression;

        return $this;
    }

    public function getMotsClesTrouves(): ?array
    {
        return $this->motsClesTrouves;
    }

    public function setMotsClesTrouves(?array $motsClesTrouves): static
    {
        $this->motsClesTrouves = $motsClesTrouves;

        return $this;
    }
    public function getEnigmes(): array
    {
        return $this->enigmes ?? [];
    }

    public function setEnigmes(array $enigmes): self
    {
        $this->enigmes = $enigmes;
        return $this;
    }

    public function getTitresEtapes(): array
    {
        return $this->titresEtapes ?? [];
    }

    public function setTitresEtapes(array $titresEtapes): self
    {
        $this->titresEtapes = $titresEtapes;
        return $this;
    }

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(?Participant $participant): static
    {
        $this->participant = $participant;

        return $this;
    }

    /**
     * @return Collection<int, Illustration>
     */
    public function getIllustrations(): Collection
    {
        return $this->illustrations;
    }

    public function addIllustration(Illustration $illustration): static
    {
        if (!$this->illustrations->contains($illustration)) {
            $this->illustrations->add($illustration);
            $illustration->setEscapeGame($this);
        }

        return $this;
    }

    public function removeIllustration(Illustration $illustration): static
    {
        if ($this->illustrations->removeElement($illustration)) {
            // set the owning side to null (unless already changed)
            if ($illustration->getEscapeGame() === $this) {
                $illustration->setEscapeGame(null);
            }
        }

        return $this;
    }

    public function getDatemajAt(): ?\DateTime
    {
        return $this->datemaj_at;
    }

    public function setDatemajAt(?\DateTime $datemaj_at): static
    {
        $this->datemaj_at = $datemaj_at;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTime $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection<int, PlaySession>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(PlaySession $session): static
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setEscapeGame($this);
        }

        return $this;
    }

    public function removeSession(PlaySession $session): static
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getEscapeGame() === $this) {
                $session->setEscapeGame(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MobileLink>
     */
    public function getMobilelink(): Collection
    {
        return $this->mobilelink;
    }

    public function addMobilelink(MobileLink $mobilelink): static
    {
        if (!$this->mobilelink->contains($mobilelink)) {
            $this->mobilelink->add($mobilelink);
            $mobilelink->setEscapeGame($this);
        }

        return $this;
    }

    public function removeMobilelink(MobileLink $mobilelink): static
    {
        if ($this->mobilelink->removeElement($mobilelink)) {
            // set the owning side to null (unless already changed)
            if ($mobilelink->getEscapeGame() === $this) {
                $mobilelink->setEscapeGame(null);
            }
        }

        return $this;
    }

}
