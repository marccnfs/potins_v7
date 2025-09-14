<?php

namespace App\Entity\Media;


use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity]
#[ORM\Table(name:"aff_imagesjpgr")]
#[ORM\HasLifecycleCallbacks]
class Imagejpg

{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Media::class, inversedBy: 'imagejpg')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Media $idmedia= null;

    #[ORM\Column(nullable: true)]
    private ?string $extension = null;

    #[ORM\Column(nullable: true)]
    private ?string $namefile = null;

    #[ORM\Column(nullable: true)]
    private ?string $namethumb = null;

    #[ORM\Column(nullable: true)]
    private ?string $alt = null;


    /** @var UploadedFile file */
    private $file;
    private $tempFilename;
    private $typefile;



   
    public function setFile($options)
    {
          $this->file = $options['file'];
          $this->alt = $options['name'];
          $this->typefile = $options['filetyp']; //soit 'file' soit '64' soit 'gif'
          if (null !== $this->namefile) {
              $this->tempFilename = $this->namefile;
              $this->ext = null;
              $this->alt = null;
          }

    }


  #[ORM\PrePersist]
  #[ORM\PreUpdate]
  public function preUpload()
  {
    if (null === $this->file) {
      return;
    }

        if ($this->typefile === "file") {
            $this->extension = $this->file->guessExtension();
            $uploadName = sha1(uniqid(mt_rand(), true));
            $this->namefile = $uploadName . '.' . $this->extension;

        }elseif ($this->typefile === "gif") {
            $this->extension = "gif";
            //$this->extension = $this->file->guessExtension();
            $this->alt = $this->file['name'];
            $uploadName = sha1(uniqid(mt_rand(), true));
            $this->namefile=$uploadName.'.'.$this->extension;
        } else {
            //$this->extension = '64';
            //$namefile=substr($this->tempinfo, strrpos($this->tempinfo,"."));  //todo le nom de l'image et lee alt
            //$this->alt = $namefile;
            $uploadName = sha1(uniqid(mt_rand(), true));
            $this->namefile = $uploadName . '.' . 'jpg';
        }

  }

    #[ORM\PostPersist]
    #[ORM\PostUpdate]
    public function upload()
      {

        if (null === $this->file) {
          return;
        }

        if(null !== $this->tempFilename){
            $oldfile=$this->getUploadRootDir().'/'.$this->tempFilename;
            if(file_exists($oldfile)){
                unlink($oldfile);
            }
        }

            if ($this->typefile === "file") {
                  $manager = new ImageManager();
                  $manager->make($this->file)
                      ->fit(450, null)
                      ->save($this->getUploadRootDir() . '/' . $this->namefile)
                      ->destroy();
              } elseif ($this->typefile === "gif") {
                try
                {
                    move_uploaded_file($this->file['tmp_name'],
                        $this->getUploadRootDir().'/'.$this->namefile
                    );
                }
                catch (FileException $e)
                {
                    echo 'erreur chargement du fichiers';
                }

              } else {
                    $parts = explode(',', $this->file);
                    $data = $parts[1];
                    $data = base64_decode($data);
                    file_put_contents($this->getUploadRootDir() . '/' . $this->namefile, $data);
            }


      }

    #[ORM\PreRemove]
    public function preRemoveUpload(): void
    {
        $this->tempFilename = $this->getUploadRootDir().'/'.$this->namefile;
      }

    #[ORM\PostRemove]
    public function removeUpload(): void
    {
        if (file_exists($this->tempFilename)) {
          unlink($this->tempFilename);
        }
      }

      public function getUploadDir(): string
      {
        return 'upload/module';
      }


      public function getUploadRootDir(): string
      {
        // On retourne le chemin relatif vers l'image pour notre code PHP

        return  __DIR__.'/../../../public/'.$this->getUploadDir();

      }

      public function getWebPath(): string
      {
        return $this->getUploadDir().'/'.$this->namefile;
      }

      public function getApiPath(): string
      {
        return 'https://affichange.com/'.$this->getUploadDir().'/'.$this->namefile;
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

      public function getFile(): UploadedFile
      {
        return $this->file;
        }

        public function getIdmedia(): ?Media
       {
           return $this->idmedia;
       }

       public function setIdmedia(?Media $idmedia): self
       {
           $this->idmedia = $idmedia;

           return $this;
       }

       public function getNamethumb(): ?string
       {
           return $this->namethumb;
       }

       public function setNamethumb(?string $namethumb): self
       {
           $this->namethumb = $namethumb;

           return $this;
       }
}
