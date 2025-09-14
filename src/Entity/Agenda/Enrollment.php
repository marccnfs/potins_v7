<?php

namespace App\Entity\Agenda;

use App\Entity\Users\Participant;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'agenda_enrollments')]
#[ORM\UniqueConstraint(columns: ['event_id','participant_id'])]
class Enrollment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')] // switchable en UUID si tu veux
    private int $id;

    #[ORM\ManyToOne(targetEntity: Event::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Event $event;

    #[ORM\ManyToOne(targetEntity: Participant::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Participant $participant;

    #[ORM\Column(length: 12)]
    private string $status = 'confirmed'; // pending|confirmed|cancelled|waitlist

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(Event $event, Participant $participant, string $status = 'confirmed')
    {
        $this->event = $event;
        $this->participant = $participant;
        $this->status = $status;
        $this->createdAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function getId(): int { return $this->id; }
    public function getEvent(): Event { return $this->event; }
    public function getParticipant(): Participant { return $this->participant; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $s): void { $this->status = $s; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function setParticipant(?Participant $participant): static
    {
        $this->participant = $participant;

        return $this;
    }
}
