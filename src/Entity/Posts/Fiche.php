<?php

namespace App\Entity\Posts;

use App\Entity\Media\Pdfstore;
use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\Table(name:"aff_fiche")]
#[ORM\HasLifecycleCallbacks]
class Fiche
{
    private $file;
    private $data;
    private $tempFilename;
    private $tempinfo;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;


    #[ORM\Column(length:255,nullable: true)]
    private ?string $extension= null;

    #[ORM\Column(length:255,nullable: true)]
    private ?string $fileblob= null;

    #[ORM\Column(length:255,nullable: true)]
    private ?string $tag= null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $datecreat;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $datemodif;

    #[ORM\Column(length: 180,nullable: true)]
    private ?string $pdfreview = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private bool $type=true;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $deleted=false;

    #[Groups(["fiche_post:read",])]
    private string $apifileblob;

    public function setFile($options): void
    {
        $this->file = $options['filesource'];
        $this->tempinfo=$options['name'];
        $this->tag=$options['tag'];

        if (null !== $this->fileblob) {
            $this->tempFilename = $this->fileblob;
            $this->extension = null;
            $this->tag = null;
        }

    }

    public function initNameFile(): bool|string
    {
        if (null === $this->file) return false;
        $this->extension = 'txt';
        $uploadName = bin2hex(random_bytes(16));
        $this->fileblob=$uploadName.'.'.$this->extension;
        $data = trim($this->file);
        $this->data=nl2br($data);
        return $this->fileblob;
    }


    public function uploadContent(): bool|int
    {
        return file_put_contents($this->getUploadRootDir().'/'.$this->fileblob, $this->data);
    }


    #[ORM\PreRemove()]
    public function preRemoveUpload(): void
    {
        $this->tempFilename = $this->getUploadRootDir().'/'.$this->fileblob;
    }

    #[ORM\PostRemove()]
    public function removeUpload(): void
    {
        if (file_exists($this->tempFilename)) {
            unlink($this->tempFilename);
        }
    }

    public function deleteFile(): void
    {
        if($this->fileblob){
            $this->tempFilename = $this->getUploadRootDir().'/'.$this->fileblob;
            if (file_exists($this->tempFilename)) {
                unlink($this->tempFilename);
            }
            $this->fileblob=null;
        }
    }

    public function getUploadDir(): string
    {
        return '5764xs4m/blobtxt8_4';
    }

    public function getUploadRootDir(): string
    {
        return  __DIR__ . '/../../../public/' .$this->getUploadDir();
    }

    public function getWebPathblob(): string
    {
        return $this->getUploadDir().'/'.$this->fileblob;
    }

    public function getphpPathblob(): string
    {
        return $this->getUploadRootDir().'/'.$this->fileblob;
    }

    public function getFileblob(): string
    {
        if($this->fileblob){
            //return  __DIR__ . '/../../../public/' .$this->fileblob;
            //return 'https://affichange.com/'.$this->getUploadDir().'/'.$this->fileblob;
            return  $this->fileblob;
        }else{
            return false;
        }
    }

    public function getApiFileblob(): string
    {
        if($this->fileblob){
            //return  $this->fileblob;
            return 'https://potinsnumeriques.fr/'.$this->getUploadDir().'/'.$this->fileblob;
        }else{
            return false;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatecreat(): ?\DateTime
    {
        return $this->datecreat;
    }

    public function setDatecreat(\DateTime $datecreat): self
    {
        $this->datecreat = $datecreat;

        return $this;
    }

    public function getDatemodif(): ?\DateTime
    {
        return $this->datemodif;
    }

    public function setDatemodif(?\DateTime $datemodif): self
    {
        $this->datemodif = $datemodif;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(?string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function setFileblob(?string $fileblob): self
    {
        $this->fileblob = $fileblob;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(?string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function getPdfreview(): ?string
    {
        return $this->pdfreview;
    }

    public function setPdfreview(string $pdfreview): static
    {
        $this->pdfreview = $pdfreview;

        return $this;
    }

    public function isType(): ?bool
    {
        return $this->type;
    }

    public function setType(bool $type): static
    {
        $this->type = $type;

        return $this;
    }
}
