<?php

namespace App\Module;

use App\Entity\Media\Imagejpg;
use App\Entity\Media\Media;
use App\Entity\Media\Pict;
use App\Entity\Module\GpReview;
use App\Entity\Posts\Fiche;
use App\Entity\Ressources\Reviews;
use App\Lib\MsgAjax;
use App\Repository\ReviewRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;

class Reviewscator
{
    private EntityManagerInterface $em;
    private ReviewRepository $reviewRepository;
    private DateTime $now;
    private GetReview $getReview;



    public function __construct(EntityManagerInterface $entityManager, ReviewRepository $reviewRepository, GetReview $getReview)
    {
        $this->em = $entityManager;
        $this->now= New DateTime();
        $this->reviewRepository = $reviewRepository;
        $this->getReview=$getReview;
    }

    public function CreateGpReview($post,$member): GpReview
    {
        $gpreview=new GpReview();
        $media=new Media();
        $post->setGpreview($gpreview);
        $gpreview->setAuthor($member);
        $gpreview->setMedia($media);
        $gpreview->setPotin($post);
        $this->em->persist($post);
        $this->em->persist($media);
        $this->em->persist($gpreview);
        $this->em->flush();
        return $gpreview;
    }

    public function AddJpg($result): array
    {
        if(!$media=$result['gpreview']->getMedia()){
            $media=New Media();
            $result['gpreview']->setMedia($media);
        }
        $media=$result['gpreview']->getMedia();
        $images=$media->getImagejpg();
        $options=['file'=>$result['file'],'filetyp'=>$result['typefile'],'name'=>'filereader'];
        $this->createmediasJpg($options, $media);
        $this->em->persist($result['gpreview']);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;

    }
    public function AddGif($result): array
    {
        if(!$media=$result['gpreview']->getMedia()){
            $media=New Media();
            $result['gpreview']->setMedia($media);
        }
        $media=$result['gpreview']->getMedia();

        $options=['file'=>$result['pict'],'filetyp'=>'file','name'=>'filereader']; //todo recupere le nom
        $images=$media->getImagejpg();
        $this->createmediasJpg($options, $media);
        $this->em->persist($result['gpreview']);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;

    }

    protected function createmediasJpg($options, $media): bool
    {
        $imagejpg = new Imagejpg();
        $imagejpg->setFile($options);
        $media->addImagejpg($imagejpg);
        return true;
    }

    public function addPict($result): array
    {
        if(!$media=$result['gpreview']->getMedia()){
            $media=New Media();
            $result['gpreview']->setMedia($media);
        }
        $media=$result['gpreview']->getMedia();
        $npict= new Imagejpg();

        if($result['pict']!="false"){
            $this->createPict($npict,$result['pict'],$media);
        }

        $this->em->persist($result['gpreview']);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

    public function createPict($npict,$pict64, Media $media=null): bool
    {
        //list($type, $data) = explode(';', $pict64);
        list(, $data)      = explode(',', $pict64);
        $parts = base64_decode($data);
        $img = imagecreatefromstring($parts);
        if($img) {
            $uploadName = bin2hex(random_bytes(16));
            $namefile = $uploadName . '.' . 'jpg';
            imagejpeg($img, $npict->getUploadRootDir() . '/' . $namefile);
            $npict->setNamefile($namefile);
        }
        return true;
    }

    public function editPict($npict,$pict64, Media $media=null): array
    {
        if($npict->getNamefile()!= null){
            $npict->removeUpload();
        }
        $this->createPict($npict,$pict64,$media);
        return MsgAjax::MSG_POSTOK;
    }

    public function majGpReview($form, $gpreview): array
    {
        $this->em->persist($gpreview);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function ManageReviewAjax($result): array|int|null
    {
        if($result['idrw']!=='0'){                  // edit fiche existante
            $rw=$this->reviewRepository->findForEdit($result['idrw']);
            if (!$rw) return MsgAjax::MSG_ERR1;
            $fiche=$rw->getFiche();

            if($fiche){
                $this->getReview->deletePdfReview($rw,$fiche);
                $fiche->deleteFile();
                $fiche->setDatemodif($this->now);
            }else{
                $fiche= new Fiche();
                $fiche->setDatecreat($this->now);
                $fiche->setDatemodif($this->now);
                $rw->setFiche($fiche);
            }

            $pict=$rw->getPict();

            if($pict){
                if($result['pict']!="false"){
                    $this->editPict($pict,$result['pict'],null);
                }
            }else{
                $pict= new Pict();
                $rw->setPict($pict);
                if($result['pict']!="false"){
                    $this->createPict($pict,$result['pict'],null);
                }
            }
        }else{
            $rw = new Reviews();
            $rw->setDeleted(false);
            $rw->setType($result['type']==='0'); // 0: fiche résume - 1: trame animation

            $fiche= new Fiche();
            $fiche->setDatecreat($this->now);
            $fiche->setDatemodif($this->now);
            $fiche->setDeleted(false);
            $rw->setFiche($fiche);
            $pict= new Pict();
            $rw->setPict($pict);

            if($result['pict']!="false"){
                $this->createPict($pict,$result['pict'],null);
            }

        }

        $rw->setTitre($result['titre']);
        $rw->setSoustitre(strip_tags($result['soustitre'], null));

        if($result['fiche']!="")  //si contenu
        {
            $options=[
                'filesource'=>$result['fiche'],
                "tag"=>$result['titre'],
                "name"=>$result['titre']];
            $fiche->setFile($options);
            $namefile=$fiche->initNameFile();
            if($namefile){
                $fiche->uploadContent();
                $dompdf=$this->getReview->newPdfReview($rw, $fiche,$result['post']);
                //$dompdf->stream();
            }else{
                $fiche->deleteFile();
            }

        }
        $rw->addGpreview($result['gpreview']);
        $this->em->persist($fiche);
        $this->em->persist($pict);
        $this->em->persist($rw);
        $this->em->persist($result['gpreview']);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

    public function testManageReviewAjaxForPdf($rw,$post)
    {
        $fiche=$rw->getFiche();
        $this->getReview->miseAjPdfReview($rw, $fiche,$post);
        $this->em->persist($rw);
        $this->em->flush();
        return $rw;
    }
    public function removeReview(Reviews $review): array
    {
        $this->em->remove($review);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

    public function manageGrReview($form, GpReview $gpreview, $reviews): array
    {

        if(!empty($reviews)){
            foreach ($gpreview->getReviews() as $oldreviews){
                $gpreview->removeReview($oldreviews);
            }
        }

        foreach ($reviews as $review) {
            foreach ($review as $rw){
                $adrw=$this->reviewRepository->find($rw);
                if($adrw){
                    $gpreview->addReview($adrw);
                }
            }
        }
        $this->em->persist($gpreview);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }


}
