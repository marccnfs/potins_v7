<?php

namespace App\Entity\Users;


use App\Entity\Games\EscapeGame;
use App\Entity\Games\PlaySession;
use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[ORM\Table(name:"aff_participant")]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 80, nullable: true)]
    private ?string $nickname = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 20)]
    private ?string $codeAtelier = null;

    #[ORM\Column(length: 4)]
    private ?string $codeSecret = null;


    #[ORM\OneToMany(targetEntity: EscapeGame::class, mappedBy: 'participant', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $escapeGames;

    #[ORM\OneToMany(targetEntity: PlaySession::class, mappedBy: 'participant')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $sessions;


    // --- Avatar (Vich) ---
    /**
     * NOTE: Ce champ n’est pas persisté, il sert au formulaire Vich.
     * @var File|null
     */
    #[Vich\UploadableField(mapping: 'participant_avatar', fileNameProperty: 'avatarName', size: 'avatarSize')]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/jpeg','image/png','image/webp','image/gif'],
        mimeTypesMessage: 'Formats acceptés: jpg, png, webp, gif (≤ 5 Mo).'
    )]
    private ?File $avatarFile = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $avatarName = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $avatarSize = null;

    // --- Préférences/consentement ---
    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $allowContact = false;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $preferences = null; // ex: {"lang":"fr","theme":"light"}

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->escapeGames = new ArrayCollection();
        $this->preferences = ['lang'=>'fr','theme'=>'light'];
        $this->sessions = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getCodeAtelier(): ?string
    {
        return $this->codeAtelier;
    }

    public function setCodeAtelier(string $codeAtelier): static
    {
        $this->codeAtelier = $codeAtelier;

        return $this;
    }

    public function getCodeSecret(): ?string
    {
        return $this->codeSecret;
    }

    public function setCodeSecret(string $codeSecret): static
    {
        $this->codeSecret = $codeSecret;

        return $this;
    }

    /**
     * @return Collection<int, EscapeGame>
     */
    public function getEscapeGames(): Collection
    {
        return $this->escapeGames;
    }

    public function addEscapeGame(EscapeGame $escapeGame): static
    {
        if (!$this->escapeGames->contains($escapeGame)) {
            $this->escapeGames->add($escapeGame);
            $escapeGame->setParticipant($this);
        }

        return $this;
    }

    public function removeEscapeGame(EscapeGame $escapeGame): static
    {
        if ($this->escapeGames->removeElement($escapeGame)) {
            // set the owning side to null (unless already changed)
            if ($escapeGame->getParticipant() === $this) {
                $escapeGame->setParticipant(null);
            }
        }

        return $this;
    }

    public function setAvatarFile(?File $file = null): void
    {
        $this->avatarFile = $file;
        if ($file !== null) {
            $this->updatedAt = new \DateTimeImmutable(); // déclenche Vich
        }
    }
    public function getAvatarFile(): ?File { return $this->avatarFile; }

    public function getAvatarName(): ?string { return $this->avatarName; }
    public function setAvatarName(?string $name): void { $this->avatarName = $name; }

    public function getAvatarSize(): ?int { return $this->avatarSize; }
    public function setAvatarSize(?int $size): void { $this->avatarSize = $size; }

    public function isAllowContact(): bool { return $this->allowContact; }
    public function setAllowContact(bool $v): self { $this->allowContact = $v; return $this; }

    public function getPreferences(): ?array { return $this->preferences ?? []; }
    public function setPreferences(?array $p): self { $this->preferences = $p; return $this; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $d): self { $this->updatedAt = $d; return $this; }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): static
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

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
            $session->setParticipant($this);
        }

        return $this;
    }

    public function removeSession(PlaySession $session): static
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getParticipant() === $this) {
                $session->setParticipant(null);
            }
        }

        return $this;
    }




}
