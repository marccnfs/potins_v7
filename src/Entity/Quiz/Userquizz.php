<?php

namespace App\Entity\Quiz;

use App\Repository\UserquizRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserquizRepository::class)]
#[ORM\Table(name:'aff_userquiz')]
class Userquizz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private $id;


    #[ORM\Column(length: 255,nullable: true)]
    private ?string $pseudo=null;

    #[ORM\ManyToOne(targetEntity: Quiz::class, inversedBy: 'usersquiz')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz= null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }

}