<?php


namespace App\Entity\Media;

use App\Repository\PictRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;


#[ORM\Entity(repositoryClass: PictRepository::class)]
#[ORM\Table(name:"aff_pict")]
class Pict
    {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $namefile = null;

    #[ORM\Column(nullable: true)]
    private ?string $alt = null;

    private $file;

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    public function getUploadDir()
    {
        return 'spaceweb/template';
    }

    public function getUploadRootDir()
    {
        // On retourne le chemin relatif vers l'image pour notre code PHP

        return  __DIR__.'/../../../public/'.$this->getUploadDir();

    }

    public function getWebPath()
    {
        return $this->getUploadDir().'/'.$this->namefile;
    }


    public function getApiPath()
    {
        return 'https://potinsnumeriques.fr/'.$this->getUploadDir().'/'.$this->namefile;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNamefile(): ?string
    {
        return $this->namefile;
    }

    public function setNamefile(string $namefile): self
    {
        $this->namefile = $namefile;

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

    public function removeUpload()
    {
        if (file_exists($this->namefile)) {
            unlink($this->getUploadRootDir(). '/' .$this->namefile);
        }
    }

}