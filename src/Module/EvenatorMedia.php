<?php

namespace App\Module;

use App\Entity\Agenda\Appointments;
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
use App\Repository\PostEventRepository;
use App\Service\Localisation\LocalisationServices;
use App\Service\Agenda\PostEventAgendaSynchronizer;
use App\Util\CalDateAppointement;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class EvenatorMedia  // todo duplicata de evenator mais pas utilisé -> voir evenatorPotin actif
{

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;
    private DateTime $now;
    private PostEventRepository $repoevent;
    private PostEvent|bool $postevent;
    private  mixed $media;
    private  mixed $titre;
    private  mixed $description;
    private  mixed $imagesource;
    private CalDateAppointement $calldate;
    private BoardRepository $websiterepo;
    private array $tabchange;
    private array $tabpartner=[];
    private array $tabadress=[];
    private array $tabdate=[];
    private mixed $dateselct;
    private LocalisationServices $locate;
    private AdressesRepository $adressrepo;
    private Adresses|null $adresse;
    private GpsRepository $gpsRepository;
    private Gps $gps;
    private PostEventAgendaSynchronizer $agendaSync;


    public function __construct(
        EntityManagerInterface $entityManager,
        BoardRepository $websiteRepository,
        PostEventRepository $postEventRepository,
        CalDateAppointement $calDate,
        LocalisationServices $locate,
        AdressesRepository $adressesRepository,
        GpsRepository $gpsRepository,
        PostEventAgendaSynchronizer $agendaSync
    )
    {
        $this->em = $entityManager;
        $this->now = New DateTime();
        $this->calldate = $calDate;
        $this->repoevent=$postEventRepository;
        $this->websiterepo=$websiteRepository;
        $this->locate=$locate;
        $this->adressrepo=$adressesRepository;
        $this->gpsRepository=$gpsRepository;
        $this->agendaSync = $agendaSync;
    }

    /**
     * @throws \Exception
     */
    protected function initEvent($data, $website): bool
    {
        $this->titre=$data['titre'];
        $this->description=strip_tags($data['description'], null);
        $this->imagesource=$data['imgdata'];
        $this->tabchange=$data['change'];
        $this->dateselct=json_decode($data['now'],true);
        $this->tabpartner=json_decode($data['partners'],true);

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
            }
        }
        return true;
    }

    /**
     * @param $data
     * @param $userdispatch
     * @param $website
     * @return array
     * @throws \Exception
     */
    public function newEvent($data,$userdispatch, $website): array
    {
        if(!$this->initEventMedia($data, $website)) return MsgAjax::MSG_POST0;


        if($this->tabchange['adress']){
            $this->tabadress=json_decode($data['adresse'],true);

            $testadresse=$this->adressrepo->findOneBy(
                ['idMap'=>$this->tabadress['properties']['id']]
            );

            if(!$testadresse){
                $result=$this->locate->adressor(new Adresses(),$this->tabadress);
                $this->adresse=$result['adress'];
                $this->gps=$result['gps'];
            }else{
                $this->adresse=$testadresse;
                $this->gps=$this->gpsRepository->findOneBy(["insee"=>$this->adresse->getInsee()]);
            }

        }else{
            $this->adresse=null;
            $this->gps=null;
        }

        if($this->tabchange['edit']){
            $this->postevent=$this->repoevent->find($data['event']);
            if (!$this->postevent) return MsgAjax::MSG_ERR1;
            $this->postevent->setDatemajAt($this->now);
            $this->media=$this->postevent->getMedia();
            $parution=$this->postevent->getAppointment();
            if($this->tabchange['partner']){
                $websitepartners=$this->postevent->getPartners();
                foreach ($websitepartners as $wb){
                    $this->postevent->removePartner($wb);
                }
            }
            $sector=$this->postevent->getSector();
            if($this->tabchange['adress']){
                $oldadresses=$sector->getAdresse();
                foreach ($oldadresses as $ad){
                    $sector->removeAdresse($ad);
                }
            }
        }else{
            $this->postevent = new PostEvent();
            $this->postevent->setKeymodule($website->getCodesite());
            $this->media= New Media();
            $this->postevent->setMedia($this->media);
            $parution = new Appointments();
            $this->postevent->setAuthor($userdispatch);
            $sector=new Sectors();
            $this->postevent->setSector($sector);
        }

        $this->postevent->setTitre($this->titre);
        $this->postevent->setDescription($this->description);

        if($this->adresse){
            $sector->addAdresse($this->adresse);
        }
        if($this->tabchange['img']){
            $etapefile = $this->AddFiles();
            if (!$etapefile) return MsgAjax::MSG_POST2;
        }
        if($this->tabchange['partner']){
            foreach ($this->tabpartner as $partner){
               $this->postevent->addPartner($this->websiterepo->find($partner['id']));
            }
        }

                // on instancie la parution(appointements)
                // ancienne methode :  $this->postevent->setAppointment($this->calldate->alongDayEvent($this->partner, $this->datestart, $this->dateend, $parution));
        $this->postevent->setAppointment($this->calldate->initAppointEvent($this->gps, $this->tabdate,$this->dateselct,$data['now'], $parution));

        $this->em->persist($sector);
        $this->em->persist($this->postevent);
        $this->em->flush();
        $this->agendaSync->syncFromPostEvent($this->postevent);
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


    /**
     * @param $idevent
     * @param $event
     * @return array
     */
    public function publiedEvent($idevent, $event): array
    {
        foreach ($event as $el){
            if($el->getId() == $idevent){
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
     * @param PostEvent $event
     * @return array
     */
    public function removeEvent(PostEvent $event): array
    {
        $this->em->remove($event->getAppointment());
        $this->em->remove($event);
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
