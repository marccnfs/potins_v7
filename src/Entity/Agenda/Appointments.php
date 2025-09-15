<?php

namespace App\Entity\Agenda;


use App\Entity\Sector\Gps;
use App\Repository\AppointmentsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Serializer\Annotation\Groups;
use \DateTime;


#[ORM\Entity(repositoryClass: AppointmentsRepository::class)]
#[ORM\Table(name: "aff_appointments")]
class Appointments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'type_appointment', type: Types::INTEGER)]
    private ?int $typeAppointment;

    #[Groups(['appointoffre:read', 'formules_post:read','edit_event','event_post:read'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $starttime;

    #[Groups(['appointoffre:read', 'formules_post:read','edit_event','event_post:read'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endtime;

    #[OneToMany(targetEntity: Periods::class, mappedBy: 'idAppointment')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $idPeriods;

    #[Groups(['appointoffre:read', 'formules_post:read','edit_event'])]
    #[OneToOne(targetEntity: Tabdate::class,cascade:['persist','remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Tabdate $tabdate = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $statut;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[JoinColumn(nullable: true)]
    private ?bool $confirmed=true;

    #[OneToMany(targetEntity: CallbacksAppoint::class, mappedBy: 'idAppointmentCb')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $frequenceCallbacks;

    #[ManyToOne(targetEntity: Gps::class)]
    #[JoinColumn(nullable: true)]
    private ?Gps $localisation;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $create_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $datemaj_at;

    public function __construct()
    {
        $this->idPeriods = new ArrayCollection();
        $this->frequenceCallbacks = new ArrayCollection();
        $this->create_at=new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStarttime(): ?\DateTimeImmutable
    {
        return $this->starttime;
    }

    public function setStarttime(?\DateTimeImmutable $starttime): self
    {
        $this->starttime = $starttime;

        return $this;
    }

    public function getEndtime(): ?\DateTimeImmutable
    {
        return $this->endtime;
    }

    public function setEndtime(?\DateTimeImmutable $endtime): self
    {
        $this->endtime = $endtime;

        return $this;
    }

    public function getStatut(): ?int
    {
        return $this->statut;
    }

    public function setStatut(int $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getConfirmed(): ?bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(?bool $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getIdPeriods(): Collection
    {
        return $this->idPeriods;
    }

    public function addIdPeriod(Periods $idPeriod): self
    {
        if (!$this->idPeriods->contains($idPeriod)) {
            $this->idPeriods[] = $idPeriod;
            $idPeriod->setIdAppointment($this);
        }

        return $this;
    }

    public function removeIdPeriod(Periods $idPeriod): self
    {
        if ($this->idPeriods->contains($idPeriod)) {
            $this->idPeriods->removeElement($idPeriod);
            // set the owning side to null (unless already changed)
            if ($idPeriod->getIdAppointment() === $this) {
                $idPeriod->setIdAppointment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getFrequenceCallbacks(): Collection
    {
        return $this->frequenceCallbacks;
    }

    public function addFrequenceCallback(CallbacksAppoint $frequenceCallback): self
    {
        if (!$this->frequenceCallbacks->contains($frequenceCallback)) {
            $this->frequenceCallbacks[] = $frequenceCallback;
            $frequenceCallback->setIdAppointmentCb($this);
        }

        return $this;
    }

    public function removeFrequenceCallback(CallbacksAppoint $frequenceCallback): self
    {
        if ($this->frequenceCallbacks->contains($frequenceCallback)) {
            $this->frequenceCallbacks->removeElement($frequenceCallback);
            // set the owning side to null (unless already changed)
            if ($frequenceCallback->getIdAppointmentCb() === $this) {
                $frequenceCallback->setIdAppointmentCb(null);
            }
        }

        return $this;
    }

    public function getCreateAt(): ?\DateTime
    {
        return $this->create_at;
    }

    public function setCreateAt(\DateTime $create_at): self
    {
        $this->create_at = $create_at;

        return $this;
    }

    public function getDatemajAt(): ?\DateTime
    {
        return $this->datemaj_at;
    }

    public function setDatemajAt(?\DateTime $datemaj_at): self
    {
        $this->datemaj_at = $datemaj_at;

        return $this;
    }

    public function getTypeAppointment(): ?int
    {
        return $this->typeAppointment;
    }

    public function setTypeAppointment(int $typeAppointment): self
    {
        $this->typeAppointment = $typeAppointment;

        return $this;
    }

    public function getLocalisation(): ?Gps
    {
        return $this->localisation;
    }

    public function setLocalisation(?Gps $localisation): self
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getTabdate(): ?Tabdate
    {
        return $this->tabdate;
    }

    public function setTabdate(?Tabdate $tabdate): self
    {
        $this->tabdate = $tabdate;

        return $this;
    }

    public function isConfirmed(): ?bool
    {
        return $this->confirmed;
    }

}
