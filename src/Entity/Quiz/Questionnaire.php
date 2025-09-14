<?php

namespace App\Entity\Quiz;

    use Doctrine\DBAL\Types\Types;
    use Doctrine\ORM\Mapping as ORM;
    use App\Repository\QuestionRepository;


    #[ORM\Entity(repositoryClass: QuestionRepository::class)]
    #[ORM\Table(name:'aff_questionnaire')]

    class Questionnaire
    {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private $id;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $question = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $optionA;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $optionB;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $optionC;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $optionD;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $correctAnswer;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $type;

    #[ORM\ManyToOne(targetEntity: Quiz::class, cascade: ['persist','remove'], inversedBy: 'questionaire')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Quiz $quiz;

    // Getters and setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(?string $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getOptionA(): ?string
    {
        return $this->optionA;
    }

    public function setOptionA(?string $optionA): static
    {
        $this->optionA = $optionA;

        return $this;
    }

    public function getOptionB(): ?string
    {
        return $this->optionB;
    }

    public function setOptionB(?string $optionB): static
    {
        $this->optionB = $optionB;

        return $this;
    }

    public function getOptionC(): ?string
    {
        return $this->optionC;
    }

    public function setOptionC(?string $optionC): static
    {
        $this->optionC = $optionC;

        return $this;
    }

    public function getOptionD(): ?string
    {
        return $this->optionD;
    }

    public function setOptionD(?string $optionD): static
    {
        $this->optionD = $optionD;

        return $this;
    }

    public function getCorrectAnswer(): ?string
    {
        return $this->correctAnswer;
    }

    public function setCorrectAnswer(?string $correctAnswer): static
    {
        $this->correctAnswer = $correctAnswer;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

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