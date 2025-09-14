<?php

namespace App\Module;

use App\Entity\Agenda\Appointments;
use App\Entity\Agenda\Tabdate;
use App\Entity\Marketplace\GpPresents;
use App\Entity\Marketplace\Offres;
use App\Entity\Media\Imagejpg;
use App\Entity\Media\Media;
use App\Entity\Module\PostEvent;
use App\Entity\Sector\Adresses;
use App\Entity\Sector\Gps;
use App\Entity\Sector\Sectors;
use App\Lib\MsgAjax;
use App\Repository\AdressesRepository;
use App\Repository\BoardRepository;
use App\Repository\GpsRepository;
use App\Repository\OffresRepository;
use App\Repository\PostEventRepository;
use App\Service\Localisation\LocalisationServices;
use App\Util\CalDateAppointement;
use App\Util\TokenGenerator;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class Shopator
{

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;
    private DateTime $now;
    private OffresRepository $repooffre;
    private  mixed $media;
    private  mixed $titre;
    private  mixed $description;
    private  mixed $imagesource;
    private CalDateAppointement $calldate;
    private array $tabchange;
    private array $tabdate=[];
    private mixed $dateselct;
    private TokenGenerator $tokenGenerator;


    public function __construct(
        EntityManagerInterface $entityManager,
        OffresRepository $offreRepository,
        CalDateAppointement $calDate,
        TokenGenerator $tokenGenerator
    )
    {
        $this->em = $entityManager;
        $this->now = New DateTime();
        $this->calldate = $calDate;
        $this->repooffre=$offreRepository;
        $this->tokenGenerator = $tokenGenerator;

    }


    public function preNewOffre(): array
    {
        $taboffre=[];
        $taboffre['id']=0;
        $taboffre['content']="";
        $taboffre['tx']="";
        return $taboffre;
    }

    protected function initOffre($data, $board): bool
    {
        $this->titre=$data['titre'];
        $this->description=strip_tags($data['description'], null);
        $this->imagesource=$data['imgdata'];
        $this->tabchange=$data['change'];
        $this->dateselct=json_decode($data['now'],true);
        return true;
    }


    public function newOffre($member,$board,$data ): array
    {
        if(!$this->initOffre($data, $board)) return MsgAjax::MSG_POST0;

        if($this->tabchange['edit']){
            $offre =$this->repooffre->find($data['offre']);
            $offre->setModifAt($this->now);
            $this->media= $offre->getMedia();
            $parution= $offre->getParution();
            $cl_Tabdate=$parution->getTabdate();
        }else{
            $offre = new Offres();
            $offre->setKeymodule($board->getCodesite());
            $offre->setCode("moncadeau:".$this->tokenGenerator->generateToken());
            $offre->setDeleted(false);
            $offre->setActive(true);
            $this->media= New Media();
            $offre->setMedia($this->media);
            $parution = new Appointments();
            $cl_Tabdate=new Tabdate();
            $offre->setAuthor($member);
            $gppresent=new GpPresents();
            $offre->setGppresents($gppresent);
        }

        $offre->setTitre($this->titre);
        $offre->setDescriptif($this->description);

        if($this->tabchange['tabdate']){
            foreach ($this->dateselct as $nday) {
                foreach ($nday as $ndate) {
                    $ndatestr = explode(",", $ndate);
                    if(intval($ndatestr[2])<10) $ndatestr[2]="0".$ndatestr[2];
                    $ndatestr[1]=strval(intval($ndatestr[1])+1); // converti le mois javascript en mois humain
                    if(intval($ndatestr[1])<10) $ndatestr[1]="0".$ndatestr[1];
                    $dateobj = new DateTimeimmutable("{$ndatestr[0]}-{$ndatestr[1]}-{$ndatestr[2]}");
                    $this->tabdate[$dateobj->getTimestamp()]=$dateobj;
                }
                ksort($this->tabdate);
            }

        }

        if($this->tabchange['img']){
            $etapefile = $this->AddFiles();
            if (!$etapefile) return MsgAjax::MSG_POST2;
        }

        $offre->setParution($this->calldate->initAppointEvent($this->now,null,$cl_Tabdate, $this->tabdate,$this->dateselct,$data['now'], $parution));
        $this->em->persist($offre);
        $this->em->persist($board);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

    protected function AddFiles(): bool
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

    protected function createmediasJpg($options): bool
    {
        $imagejpg = new Imagejpg();
        $imagejpg->setFile($options);
        $this->media->addImagejpg($imagejpg);
        return true;
    }


    public function publiedOffre($idoffre, $offre): array
    {
        foreach ($offre as $el){
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


    public function removeOffre(Offres $offre): array
    {
        $this->em->remove($offre->getParution());
        $this->em->remove($offre);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

    /**
     * @param PostEvent $event
     * @return array
     */
    public function publiedOneEvent(PostEvent $event): array
    {
        $event->setPublied(!$event->getPublied());
        $this->em->persist($event);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

}