<?php

namespace App\Entity\Agenda;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;


#[ORM\Entity]
#[ORM\Table(name: "aff_callbackappoint")]
class CallbacksAppoint
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ManyToOne(targetEntity: Appointments::class,inversedBy: 'frequenceCallbacks')]
    #[JoinColumn(nullable: false)]
    private ?Appointments $idAppointmentCb;

    #[ORM\Column(name: 'choice_callback', type: Types::SMALLINT)]
    #[JoinColumn(nullable: false)]
    private ?int $choiceCallback;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChoiceCallback(): ?int
    {
        return $this->choiceCallback;
    }

    public function setChoiceCallback(int $choiceCallback): self
    {
        $this->choiceCallback = $choiceCallback;

        return $this;
    }

    public function getIdAppointmentCb(): ?Appointments
    {
        return $this->idAppointmentCb;
    }

    public function setIdAppointmentCb(?Appointments $idAppointmentCb): self
    {
        $this->idAppointmentCb = $idAppointmentCb;

        return $this;
    }

}
