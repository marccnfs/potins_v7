<?php

namespace App\Entity\Media;


use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


#[ORM\Entity]
#[ORM\Table(name:"aff_pdfstore")]
#[ORM\HasLifecycleCallbacks]
class Pdfstore
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $alt = null;

    #[ORM\Column(nullable: true)]
    private ?string $extension = null;

    private $file;
    private $temp;
    private $namelibraryfile;


    public function setPdf($pdf): void
    {
      $this->file = $pdf;

        if (null !== $this->name) {
          $this->temp = $this->name;
          $this->extension = null;
          $this->alt = null;
        }

    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function preUpload(): void
    {
        if (null === $this->file) {
          return;
        }
        $this->extension = "pdf";
        $this->alt = $this->file['name'];
        $uploadName = bin2hex(random_bytes(16));
        $this->name=$uploadName.'.'.$this->extension;

      }

    #[ORM\PostPersist]
    #[ORM\PostUpdate]
    public function upload(): void
    {

    if (null === $this->file) {
      return;
    }

    try
    {
        move_uploaded_file($this->file['tmp_name'],
            $this->getUploadRootDir().'/'.$this->name
        );
    }
    catch (FileException $e)
    {
        echo 'erreur chargement du fichiers';
    }

  }

    #[ORM\PreRemove]
    public function preRemoveUpload(): void
    {
          $temp =null;
          $this->$temp = $this->getUploadRootDir().$this->name;
      }

    #[ORM\PostRemove]
    public function removeUpload(): void
    {

        if (file_exists($this->temp)) {
          unlink($this->temp);
        }
      }

  public function getUploadDir(): string
  {
    return 'uploads/storepdf';
  }

  protected function getUploadRootDir(): string
  {
    // On retourne le chemin relatif vers l'image pour notre code PHP

    return  __DIR__.'/../../public/'.$this->getUploadDir();

  }

  public function getPdfPath(): string
  {
    return $this->getUploadDir().'/'.$this->name;
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
      return $this->namelibraryfile;
  }

  public function setName(string $namefile): self
  {
      $namelibraryfile =null;
      $this->namelibraryfile = $namelibraryfile;

      return $this;
  }

  public function getAlt(): ?string
  {
      return $this->alt;
  }

  public function setAlt(string $alt): self
  {
      $this->alt = $alt;

      return $this;
  }
    public function getFile()
    {
    return $this->file;
    }

}
