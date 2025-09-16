<?php

namespace App\Module;


use App\Entity\Media\DocEvent;
use App\Entity\Media\Docstore;
use App\Entity\Media\Pict;
use App\Lib\MsgAjax;
use Doctrine\ORM\EntityManagerInterface;

class EvenatorDoc
{

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;



    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function addDoc($data,$event): array
    {
        $doc = new DocEvent();
        $event->addDoc($doc);
        if($data['docfile']!="false"){
            $ndoc= new Docstore();
            $ndoc->setDoc($data['docfile'], $data['type']);
            $doc->setFichier($ndoc->getUploadDir());
            $doc->setDoc($ndoc);
        }
        $doc->setFichier($data['fichier']);
        $doc->setType($data['type']);
        if($data['pict']!="false"){
            $npict= new Pict();
            $this->createPict($npict,$data['pict'],$doc);
        }
        $this->em->persist($doc);
        $this->em->persist($event);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

    public function createPict($npict,$pict64, DocEvent $doc): bool
    {
        list(, $data)      = explode(',', $pict64);
        $parts = base64_decode($data);
        $img = imagecreatefromstring($parts);
        if($img) {
            $uploadName = bin2hex(random_bytes(16));
            $namefile = $uploadName . '.' . 'jpg';
            imagejpeg($img, $npict->getUploadRootDir() . '/' . $namefile);
            $npict->setNamefile($namefile);
            $doc->setPict($npict);
        }
        return true;
    }

    public function editPict($npict,$pict64, DocEvent $doc): array
    {
        if($npict->getNamefile()!= null){
            $npict->removeUpload();
        }
        $this->createPict($npict,$pict64,$doc);
        return MsgAjax::MSG_POSTOK;
    }
}
