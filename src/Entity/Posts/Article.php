<?php

namespace App\Entity\Posts;

use App\Entity\Ressources\Ressources;
use App\Repository\ArticleRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;


#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\Table(name:"aff_article")]
#[Vich\Uploadable]
class Article
{
    private $file;
    private $tempinfo;
    private $tempFilename;
    private $data;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'htmlcontent')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Post $potin= null;

    #[ORM\ManyToOne(targetEntity: Ressources::class, inversedBy: "htmlcontent")]
    #[ORM\JoinColumn(nullable: true)]
    private ?Ressources $ressource = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titre= null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $contenu= null;

    #[ORM\Column(length:255,nullable: true)]
    private ?string $namearticle= null;

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

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $deleted=false;

    #[Vich\UploadableField(mapping: "articles_media", fileNameProperty: "mediaName")]
    private ?File $mediaFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $media = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $mediaName = null;

    public function __construct()
    {
        $this->datecreat=new DateTime();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setMediaFile(?File $file = null): void
    {
        $this->mediaFile = $file;
    }

    public function getMediaFile(): ?File
    {
        return $this->mediaFile;
    }

    public function getMediaName(): ?string
    {
        return $this->mediaName;
    }
    public function setMediaName(?string $mediaName): static
    {
        $this->mediaName = $mediaName;

        return $this;
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

    public function getPotin(): ?Post
    {
        return $this->potin;
    }

    public function setPotin(?Post $potin): static
    {
        $this->potin = $potin;

        return $this;
    }

    public function getNamearticle(): ?string
    {
        return $this->namearticle;
    }

    public function setNamearticle(?string $namearticle): static
    {
        $this->namearticle = $namearticle;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getMedia(): ?string
    {
        return $this->media;
    }

    public function setMedia(?string $media): static
    {
        $this->media = $media;

        return $this;
    }

    public function getRessource(): ?Ressources
    {
        return $this->ressource;
    }

    public function setRessource(?Ressources $ressource): static
    {
        $this->ressource = $ressource;

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

    public function preRemoveUpload(): void
    {
        $this->tempFilename = $this->getUploadRootDir().'/'.$this->fileblob;
    }


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
    public function setFile($options): void
    {
        $this->file = $options['filesource'];
        $this->tempinfo=$options['name'];
        $this->namearticle=$options['name'];
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
        return $this->data;
    }


    public function uploadContent(): bool|int
    {
        return file_put_contents($this->getUploadRootDir().'/'.$this->fileblob, $this->data);
    }


}
