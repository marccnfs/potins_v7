<?php

namespace App\Entity\Agenda;


use App\Repository\PeriodsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[ORM\Entity(repositoryClass: PeriodsRepository::class)]
#[ORM\Table(name: "aff_periods")]
class Periods
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ManyToOne(targetEntity: Appointments::class,inversedBy: 'idPeriods')]
    private ?Appointments $idAppointment;

    #[ORM\Column(type: Types::SMALLINT)]
    #[JoinColumn(nullable: false)]
    private ?int $periodeChoice;

    #[ORM\Column(type: Types::SMALLINT)]
    #[JoinColumn(nullable: false)]
    private ?int $numberrept;

    #[ORM\Column(type: Types::SMALLINT)]
    #[JoinColumn(nullable: true)]
    private ?int $typerept;

    #[ORM\Column]
    #[JoinColumn(nullable: true)]
    private ?string $daysweek;

    #[ORM\Column]
    #[JoinColumn(nullable: true)]
    private ?string $daymonth;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $startPeriod;

    #[ORM\Column]
    #[JoinColumn(nullable: true)]
    private ?string $alongPeriod;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPeriodeChoice(): ?int
    {
        return $this->periodeChoice;
    }

    public function setPeriodeChoice(int $periodeChoice): self
    {
        $this->periodeChoice = $periodeChoice;

        return $this;
    }

    public function getNumberrept(): ?int
    {
        return $this->numberrept;
    }

    public function setNumberrept(int $numberrept): self
    {
        $this->numberrept = $numberrept;

        return $this;
    }

    public function getTyperept(): ?int
    {
        return $this->typerept;
    }

    public function setTyperept(?int $typerept): self
    {
        $this->typerept = $typerept;

        return $this;
    }

    public function getDaysweek(): ?string
    {
        return $this->daysweek;
    }

    public function setDaysweek(?string $daysweek): self
    {
        $this->daysweek = $daysweek;

        return $this;
    }

    public function getDaymonth(): ?string
    {
        return $this->daymonth;
    }

    public function setDaymonth(?string $daymonth): self
    {
        $this->daymonth = $daymonth;

        return $this;
    }

    public function getStartPeriod(): ?\DateTime
    {
        return $this->startPeriod;
    }

    public function setStartPeriod(?\DateTime $startPeriod): self
    {
        $this->startPeriod = $startPeriod;

        return $this;
    }

    public function getAlongPeriod(): ?string
    {
        return $this->alongPeriod;
    }

    public function setAlongPeriod(?string $alongPeriod): self
    {
        $this->alongPeriod = $alongPeriod;

        return $this;
    }

    public function getIdAppointment(): ?Appointments
    {
        return $this->idAppointment;
    }

    public function setIdAppointment(?Appointments $idAppointment): self
    {
        $this->idAppointment = $idAppointment;

        return $this;
    }

    
}
