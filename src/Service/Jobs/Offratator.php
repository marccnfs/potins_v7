<?php


namespace App\Service\Jobs;

use App\Entity\Marketplace\Noticeproducts;
use App\Entity\Marketplace\Offres;
use App\Entity\Media\Imagejpg;
use App\Entity\Media\Media;
use App\Entity\Job\Offer;
use App\Entity\Boards\Board;
use App\Lib\MsgAjax;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class Offratator
{

    private $offer;
    private $article;
    /** @var Media $media */
    private $media;
    /** @var Board $website */
    private $website;
    private $titre;
    private $contenthtml;
    private $imagesource;
    private $em;
    private $dispatch;
    private $reference;
    private $contrat;
    private $now;


    /**
     * Postar constructor.
     * @param EntityManagerInterface $entityManager
     * @throws \Exception
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em=$entityManager;
        $this->now= New DateTime();
    }

    protected function initOffer($result){
        $this->dispatch=$result['dispatch'];
        $this->website=$result['website'];
        $this->titre=$result['titre'];
        $this->reference=$result['reference'];
        $this->titre=$result['contrat'];
        $this->contrat=$result['profil'];
        $this->contenthtml=$result['contenthtml'];
        $this->offer=$result['offer'];
        $this->imagesource=$result['imagesource'];
        return true;
    }

    /**
     * @param $result
     * @return array
     * @throws \Exception
     */
    public function newOffer($result){
        if(!$this->initOffer($result)) return MsgAjax::MSG_POST0;
        if($this->offer){  // la post existe déja
            $this->offer=$this->em->getRepository('App:Module\Postation')->find($this->offer);
            if (!$this->offer) return MsgAjax::MSG_ERR1;
            $this->offer->setModifAt($this->now);
            $this->article=$this->offer->getHtmlcontent();
            $this->article->removeUpload();
            $this->article->setDatemodif($this->now);
            $this->media=$this->offer->getMedia();
        }else{
            $this->offer = new Offres();
            $this->offer->setDeleted(false);
            $this->offer->setAuthor($this->dispatch);
            $this->article= new Noticeproducts();
            $this->article->setDatecreat($this->now);
            $this->article->setDeleted(false);
            $this->media= New Media();
            $this->offer->setMedia($this->media);
        }
        if(!$this->offer) return MsgAjax::MSG_POST1;
        $this->offer->setTitre($this->titre);
        $this->offer->setRefrence($this->reference);
        $this->offer->setContrat($this->contrat);
        $this->offer->setProfil($this->profil);

        if($this->contenthtml!="")
        {
            $options=[
                'filesource'=>$this->contenthtml,
                "tag"=>$this->titre,
                "name"=>$this->titre];
            $this->article->setFile($options);
        }
        $this->offer->setHtmlcontent($this->article);

        if($this->imagesource!="false"){
            $etapefile = $this->AddFiles();
            if (!$etapefile) return MsgAjax::MSG_POST2;
        }
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

    protected function AddFiles()
    {
        $options=['file'=>$this->imagesource,'filetyp'=>'64','name'=>'filereader']; //todo recupere le nom
        $images=$this->media->getImagejpg();
        if(count($images)>0){
            foreach ($images as $image){
                $this->media->removeImagejpg($image);  //todo pour l'instant je supprime toutes les images pour eviter des erreurs !!
            }
        }
        $this->createmediasJpg($options);
        return true;
    }

    protected function createmediasJpg($options){
        $imagejpg = new Imagejpg();
        $imagejpg->setFile($options);
        $this->media->addImagejpg($imagejpg);
        return true;
    }

    /**
     * @param Offres $offres
     * @return array
     */
    public function publiedOffre(Offres $offres): array
    {
        $offres->setPublied(!$offres->getPublied());
        $this->em->persist($offres);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

}