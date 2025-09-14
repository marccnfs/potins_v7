<?php

namespace App\Entity\Quiz;

use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
#[ORM\Table(name:'aff_quiz')]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private $id;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $name=null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $qrCode=null;

    #[ORM\OneToMany(mappedBy: 'quiz', targetEntity: Userquizz::class)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $usersquiz;

    #[ORM\OneToMany(mappedBy: 'quiz', targetEntity: Questionnaire::class, cascade: ['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $questionaire;

    public function __construct()
    {
        $this->usersquiz = new ArrayCollection();
        $this->questionaire = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQrCode(): ?string
    {
        return $this->qrCode;
    }

    public function setQrCode(?string $qrCode): static
    {
        $this->qrCode = $qrCode;

        return $this;
    }

    /**
     * @return Collection<int, Userquizz>
     */
    public function getUsersquiz(): Collection
    {
        return $this->usersquiz;
    }

    public function addUsersquiz(Userquizz $usersquiz): static
    {
        if (!$this->usersquiz->contains($usersquiz)) {
            $this->usersquiz->add($usersquiz);
            $usersquiz->setQuiz($this);
        }

        return $this;
    }

    public function removeUsersquiz(Userquizz $usersquiz): static
    {
        if ($this->usersquiz->removeElement($usersquiz)) {
            // set the owning side to null (unless already changed)
            if ($usersquiz->getQuiz() === $this) {
                $usersquiz->setQuiz(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Questionnaire>
     */
    public function getQuestionaire(): Collection
    {
        return $this->questionaire;
    }

    public function addQuestionaire(Questionnaire $questionaire): static
    {
        if (!$this->questionaire->contains($questionaire)) {
            $this->questionaire->add($questionaire);
            $questionaire->setQuiz($this);
        }

        return $this;
    }

    public function removeQuestionaire(Questionnaire $questionaire): static
    {
        if ($this->questionaire->removeElement($questionaire)) {
            // set the owning side to null (unless already changed)
            if ($questionaire->getQuiz() === $this) {
                $questionaire->setQuiz(null);
            }
        }

        return $this;
    }
}