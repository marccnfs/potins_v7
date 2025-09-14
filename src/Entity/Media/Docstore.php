<?php

namespace App\Entity\Media;

use App\Entity\Admin\OrderProducts;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: DocstoreRepository::class)]
#[ORM\Table(name: "aff_docstore")]
#[ORM\HasLifecycleCallbacks]
class Docstore
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?string $nomOriginal = null;

    #[ORM\Column(nullable: true)]
    private ?float $taille = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateEnvoi = null;

    #[ORM\Column(nullable: true)]
    private ?string $extension = null;

    private ?string $temp = null;

    #[ORM\ManyToOne(targetEntity: OrderProducts::class, inversedBy: 'docs')]
    #[ORM\JoinColumn(nullable: true)]
    private ?OrderProducts $product = null;


    public function removeUpload(): bool
    {
        $temp = null;
        $this->$temp = $this->getUploadRootDir() . $this->name;
        if (file_exists($this->temp)) {
            unlink($this->temp);
        }
       return true;
    }

    public function getUploadDir(): string
    {
        return 'uploads/storedoc';
    }

    protected function getUploadRootDir(): string
    {
        return __DIR__ . '/../../../public/' . $this->getUploadDir();
    }

    public function getWebPath(): string
    {
        return $this->getUploadDir() . '/' . $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

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

    public function getProduct(): ?OrderProducts
    {
        return $this->product;
    }

    public function setProduct(?OrderProducts $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getNomOriginal(): ?string
    {
        return $this->nomOriginal;
    }

    public function setNomOriginal(?string $nomOriginal): static
    {
        $this->nomOriginal = $nomOriginal;

        return $this;
    }

    public function getTaille(): ?float
    {
        return $this->taille;
    }

    public function setTaille(?float $taille): static
    {
        $this->taille = $taille;

        return $this;
    }

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->dateEnvoi;
    }

    public function setDateEnvoi(?\DateTimeInterface $dateEnvoi): static
    {
        $this->dateEnvoi = $dateEnvoi;

        return $this;
    }

}