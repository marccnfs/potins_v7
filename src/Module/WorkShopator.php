<?php
// derniere mise a jour : 15/04/22

namespace App\Module;


use App\Entity\Marketplace\DescriptProduct;
use App\Entity\Marketplace\Noticeproducts;
use App\Entity\Marketplace\Offres;
use App\Entity\Media\Imagejpg;
use App\Entity\Media\Media;
use App\Entity\Module\TabpublicationMsgs;
use App\Entity\UserMap\Taguery;
use App\Lib\MsgAjax;
use App\Lib\Tools;
use App\Repository\OffresRepository;
use App\Repository\TagueryRepository;
use App\Util\CalDateAppointement;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;



class WorkShopator
{

    private EntityManagerInterface $em;
    private DateTime $now;
    private TagueryRepository $tagueryRepository;
    private CalDateAppointement $evenator;
    private OffresRepository $repoffre;

    /**
     * WorkShopator constructor.
     * @param EntityManagerInterface $entityManager
     * @param TagueryRepository $tagueryRepository
     * @param OffresRepository $offrerepro
     * @param CalDateAppointement $evenator
     */
    public function __construct(EntityManagerInterface $entityManager, TagueryRepository $tagueryRepository, OffresRepository $offrerepro, CalDateAppointement $evenator)
    {
        $this->em = $entityManager;
        $this->now = New DateTime();
        $this->tagueryRepository = $tagueryRepository;
        $this->evenator = $evenator;
        $this->repoffre=$offrerepro;
    }

    /**
     * @param $offre Offres
     * @return array
     */
    public function preEditWorkShop(Offres $offre): array
    {
        $taboffre=[];
        $taboffre['id']=$offre->getId();
        $tabcarc=explode(',',$offre->getProduct()->getTabcarac());
        $taboffre['etat']=$tabcarc[0];

        if($offre->getProduct()->getHtmlcontent()->getFileblob() && file_exists($offre->getProduct()->getHtmlcontent()->getWebPathblob())){
            $taboffre['content']=file_get_contents($offre->getProduct()->getHtmlcontent()->getWebPathblob());
        }else{
            $taboffre['content']="";
        }

        if($offre->getProduct()->getNameproduct()!=null){
            $taboffre['prod']=true;
            $taboffre['more']=true;
            $taboffre['tx']="";
            foreach ($offre->getProduct()->getTagueries() as $tag){
                $taboffre['tx'].=html_entity_decode ($tag->getName()).",";
            }
        }

        if($offre->getProduct()->getUrlproduct()!=null){
            $taboffre['link']=true;
            $taboffre['more']=true;
        }

        if($offre->Ispromo()){
            $taboffre['promo']=true;
            $taboffre['more']=true;
            $taboffre['promonat']=$tabcarc[1];
        }else{
            $taboffre['promostat']=false;
            $taboffre['promonat']="choice";
        }
        return $taboffre;
    }

    /**
     * @return array
     */
    public function preNewWorkShop(): array
    {
        $taboffre=[];
        $taboffre['id']=0;
        $taboffre['etat']="neuf";
        $taboffre['content']="";
        $taboffre['prod']=false;
        $taboffre['more']=false;
        $taboffre['tx']="";
        $taboffre['link']=false;
        $taboffre['promo']=false;
        $taboffre['promostat']=false;
        $taboffre['promonat']="choice";
        return $taboffre;
    }


    /**
     * @param $dispatch
     * @param $board
     * @param $q
     * @param $tab
     * @return array
     */
    public function newWorkShop($dispatch, $board, $q, $tab): array
    {
        $taboffre = get_object_vars($tab);

        if ($taboffre['id']!=0) {  // l'offre' existe déja
            if (!$offre = $this->repoffre->findOffreToEdit($taboffre['id']))return MsgAjax::MSG_ERR1;
            $offre->setModifAt($this->now);
            $offre->setKeymodule($board->getCodesite()); // todo a supprimer apres les mise a jour
            $offre->setLocalisation($board->getLocality());
            $noticeproduct = $offre->getProduct();
            $descriptproduct = $noticeproduct->getHtmlcontent();
            $media = $offre->getMedia();
        } else {
            $offre = new Offres();
            $media =new Media();
            $offre->setMedia($media);
            $tabmsg=new TabpublicationMsgs();
            $offre->setTbmessages($tabmsg);
            $tabmsg->setOffre($offre);
            $offre->setDeleted(false);
            $offre->setActive(true);
            $offre->setKeymodule($board->getCodesite());
            $offre->setLocalisation($board->getLocality());
            $offre->setAuthor($dispatch);
            // todo ici je crée un nouveau produit à chaque fois (mais on pourrait rechercher si le produit existe déjà)
            $noticeproduct = new Noticeproducts();
            $descriptproduct = new DescriptProduct();
            $descriptproduct->setDatecreat($this->now);
            $descriptproduct->setDeleted(false);
            $noticeproduct->setHtmlcontent($descriptproduct);
            $offre->setProduct($noticeproduct);
        }

        $offre->setTitre($q['titreoffre']);
        $offre->setDescriptif($q['legende']);
        $noticeproduct->setUnit(intval($q['unit']));
        $noticeproduct->setOldprice(intval($q['oldprice']));
        $noticeproduct->setDisponible(true);
        $noticeproduct->setRemisable(true);
        $noticeproduct->setNameproduct($q['nameproduct']??null);
        $noticeproduct->setDescription($q['designprod']??null);

        //tagueries
        if(!$taguerilist=$noticeproduct->getTagueries())$taguerilist=[];
        foreach ($taguerilist as $sup){
            $noticeproduct->removeTaguery($sup);
        }
        $tags=Tools::cleanTags($q['tagsprod']);

        foreach ($tags as $tag){
            if(!$resulttag=$this->tagueryRepository->findOneBy([ 'name'=>$tag])){
                $resulttag=New Taguery();
                $resulttag->setName($tag);
                $resulttag->setPhylo($q['titreoffre']);
            }
            $noticeproduct->addTaguery($resulttag);
        }

        $noticeproduct->setUrlproduct($q['urlprod']); //todo test sur l'url
        $noticeproduct->setIdproduct($q['idprod']!=="" ?? "");


       // if($this->taboffre['promo']){}

        if(intval($q['price'])>0){
            $offre->setPromo(true);
            $noticeproduct->setTabcarac($taboffre['etat'].",".$q['natpromo']);
            $noticeproduct->setPrice(intval($q['price']));
         }else {
            $offre->setPromo(false);
            $noticeproduct->setTabcarac($taboffre['etat'] . ",choice");
        }

        if($descriptproduct->getFileblob()!=null) $descriptproduct->removeUpload();

        // on instancie la parution(appointements)
        $offre->setParution($this->evenator->daysParutions($q['appointments'], $offre));
        $offre->setTabunique($q['appointments']);
        $offre->setAdverse($q['titreoffre']);

        //$tet=$tttu;

        if ($q['contenthtml'] != "") {
            $options = [
                'filesource' => $q['contenthtml'],
                "tag" => $q['titreoffre'],
                "name" => $q['titreoffre']];
            $descriptproduct->setFile($options);
            $data=$descriptproduct->initNameFile();
            if($data){
                $descriptproduct->uploadContent();
            }else{
                $descriptproduct->deleteFile();
            }
        }
        $noticeproduct->setHtmlcontent($descriptproduct);

        if ($q['file64'] != "false") {
            $etapefile = $this->AddFiles($q['file64'],$media);
            if (!$etapefile) return MsgAjax::MSG_POST2;
        }
        $this->em->persist($offre);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

    protected function AddFiles($imagesource, $media): bool
    {
        $options=['file'=>$imagesource,'filetyp'=>'64','name'=>'filereader']; //todo recupere le nom
        $images=$media->getImagejpg();
        if(count($images)>0){
            foreach ($images as $image){
                $media->removeImagejpg($image);  //todo pour l'instant je supprime toutes les images pour eviter des erreurs !!
            }
        }
        $this->createmediasJpg($options, $media);
        return true;
    }

    protected function createmediasJpg($options, $media): bool
    {
        $imagejpg = new Imagejpg();
        $imagejpg->setFile($options);
        $media->addImagejpg($imagejpg);
        return true;
    }

    /**
     * @param $idoffre
     * @param Offres $offres
     * @return array
     */
    public function publiedWorkShop($idoffre, Offres $offres): array
    {
        foreach ($offres as $el){
            if($el->getId() == $idoffre){
                if($el->getPublied()){
                    $el->setPublied(false);
                }else{
                    $el->setPublied(true);
                }
            } else{
                $el->setPublied(false);
            }
            $this->em->persist($el);
        }
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;

    }

    /**
     * @param Offres $offres
     * @return array
     */
    public function publiedOneWorkShop(Offres $offres): array
    {
        $offres->setPublied(!$offres->getPublied());
        $this->em->persist($offres);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }
}