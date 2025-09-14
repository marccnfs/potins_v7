<?php


namespace App\Service\Media;


use App\Entity\Media\Pict;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Uploadator
{
    private $file;
    private $tempFilename;
    private $ext;
    private $newfile;
    /**
     * @var Pict
     */
    private $pict;


    /**
     * @param UploadedFile $file
     * @param $pict
     */
    public function Upload(UploadedFile $file, $pict)
    {

        $this->pict=$pict;
        $this->file=$file;
        if (null !== $this->pict->getNamefile()) {
            $this->tempFilename = $this->pict->getNamefile();
            $this->ext = null;
        }

        $this->ext=$this->file->guessExtension();
        $this->pict->SetAlt($this->file->getClientOriginalName());
        $uploadName = sha1(uniqid(mt_rand(), true));
        $this->newfile=$uploadName . '.' . $this->ext;
        $this->pict->SetNamefile($this->newfile);

        if(null !== $this->tempFilename){
            $oldfile=$this->pict->getUploadRootDir().'/'.$this->tempFilename;

            if(file_exists($oldfile)){
                unlink($oldfile);
            }
        }
        $manager = new ImageManager();
        $manager->make($this->file)
            ->resize(400,null, function($constraint){
                $constraint->aspectRatio();
            })
           // ->fit(450, null)
            ->save($this->pict->getUploadRootDir() . '/' . $this->newfile)
            ->destroy();
        //old version
        //$this->file->move(
         //   $this->pict->getUploadRootDir(),
         //   $this->newfile);

        return;
    }


    /**
     * @param $pict Pict
     */
    public function removeUpload($pict)
    {
        $this->tempFilename = $pict->getUploadRootDir().'/'.$pict->getNamefile();
        if (file_exists($this->tempFilename)) {
            unlink($this->tempFilename);
        }
    }

}