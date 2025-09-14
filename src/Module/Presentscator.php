<?php

namespace App\Module;

use App\Entity\Marketplace\Presents;
use App\Entity\Media\Pict;
use App\Entity\Posts\Article;
use App\Lib\MsgAjax;
use App\Repository\CategoriesRepository;
use App\Repository\OffresRepository;
use App\Repository\PresentsRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class Presentscator
{
    private EntityManagerInterface $em;
    private PresentsRepository $presentRepository;
    private DateTime $now;
    private CategoriesRepository $categoriesRepository;
    private OffresRepository $offresRepository;


    public function __construct(OffresRepository $offresRepository,EntityManagerInterface $entityManager, PresentsRepository $presentRepository, CategoriesRepository $categoriesRepository)
    {
        $this->em = $entityManager;
        $this->now= New DateTime();
        $this->presentRepository = $presentRepository;
        $this->categoriesRepository=$categoriesRepository;
        $this->offresRepository=$offresRepository;
    }


    public function ManagePresentAjax($result,$key): array|int|null
    {

        if($result['edit']==="1"){
            $pres=$this->presentRepository->findForEdit($result['edit'])[0];
            if (!$pres) return MsgAjax::MSG_ERR1;

            $article=$pres->getHtmlcontent();

            if($article){
                $article->deleteFile();
                $article->setDatemodif($this->now);
            }else{
                $article= new Article();
                $article->setDatecreat($this->now);
                $article->setDatemodif($this->now);
                $pres->setHtmlcontent($article);
            }

            $npict=$pres->getPict();
            if($result['pict']!="false"){
                $this->editPict($npict,$result['pict'],$pres);
            }
            if($result['cat']!=""){
                $pres->setCategorie($this->categoriesRepository->find($result['cat']));
            }
        }else{
            $offre=$this->offresRepository->findOneOffre($result['offre']);
            $gppresents=$offre->getGppresents();

            $pres = new Presents();
            $pres->addGppresent($gppresents);
            $pres->setDeleted(false);
            $article= new Article();
            $article->setDatecreat($this->now);
            $article->setDatemodif($this->now);
            $article->setDeleted(false);
            $pres->setHtmlcontent($article);
            $npict= new Pict();

            if($result['pict']!="false"){
                $this->createPict($npict,$result['pict'],$pres);
            }
            $pres->setCategorie($this->categoriesRepository->find($result['cat']));

        }

        $pres->setTitre($result['titre']);
        $pres->setComposition(strip_tags($result['compo'], null));
        $pres->setDescriptif(strip_tags($result['descriptif'], null));


        if($result['contenthtml']!="")
        {
            $options=[
                'filesource'=>$result['contenthtml'],
                "tag"=>$result['titre'],
                "name"=>$result['titre']];
            $article->setFile($options);
            $data=$article->initNameFile();
            if($data){
                $article->uploadContent();
            }else{
                $article->deleteFile();
            }
        }
        $this->em->persist($article);
        $this->em->persist($pres);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }


    public function createPict($npict,$pict64, Presents $presents): bool
    {
            //list($type, $data) = explode(';', $pict64);
            list(, $data)      = explode(',', $pict64);
            $parts = base64_decode($data);
            $img = imagecreatefromstring($parts);
            if($img) {
                $uploadName = sha1(uniqid(mt_rand(), true));
                $namefile = $uploadName . '.' . 'jpg';
                imagejpeg($img, $npict->getUploadRootDir() . '/' . $namefile);
                $npict->setNamefile($namefile);
                $presents->setPict($npict);
            }
        return true;
    }

    public function editPict($npict,$pict64, Presents $presents): array
    {
            if($npict->getNamefile()!= null){
                $npict->removeUpload();
            }
            $this->createPict($npict,$pict64,$presents);
        return MsgAjax::MSG_POSTOK;
    }

 
    public function removeRessource(Presents $presents): array
    {
        $this->em->remove($presents);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

}