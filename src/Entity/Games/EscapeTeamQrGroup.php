<?php

namespace App\Entity\Games;

use App\Repository\EscapeTeamQrGroupRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EscapeTeamQrGroupRepository::class)]
#[ORM\Table(name: 'aff_escape_team_qr_group')]
class EscapeTeamQrGroup
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: EscapeTeamRun::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?EscapeTeamRun $run = null;

    #[ORM\Column(length: 150)]
    private string $name = '';

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    /** @var Collection<int, EscapeTeamQrPage> */
    #[ORM\OneToMany(mappedBy: 'group', targetEntity: EscapeTeamQrPage::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $pages;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->pages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRun(): ?EscapeTeamRun
    {
        return $this->run;
    }

    public function setRun(?EscapeTeamRun $run): static
    {
        $this->run = $run;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = trim($name);

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, EscapeTeamQrPage>
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(EscapeTeamQrPage $page): static
    {
        if (!$this->pages->contains($page)) {
            $this->pages->add($page);
            $page->setGroup($this);
        }

        return $this;
    }

    public function removePage(EscapeTeamQrPage $page): static
    {
        if ($this->pages->removeElement($page)) {
            if ($page->getGroup() === $this) {
                $page->setGroup(null);
            }
        }

        return $this;
    }
}
