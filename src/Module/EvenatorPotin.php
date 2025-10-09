<?php

namespace App\Module;

use App\Entity\Agenda\Appointments;
use App\Entity\Agenda\Tabdate;
use App\Entity\Boards\Board;
use App\Entity\Module\PostEvent;
use App\Entity\Posts\Post;
use App\Entity\Sector\Sectors;
use App\Lib\MsgAjax;
use App\Repository\BoardRepository;
use App\Repository\PostEventRepository;
use App\Repository\PostRepository;
use App\Service\Agenda\PostEventAgendaSynchronizer;
use App\Util\CalDateAppointement;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;


//todo voir evenatorMedia pour code sur les adresses et sector
class EvenatorPotin
{

    private EntityManagerInterface $em;
    private DateTime $now;
    private PostEventRepository $repoevent;
    private PostEvent|bool $postevent;
    private  mixed $description;
    private CalDateAppointement $calldate;
    private BoardRepository $boardrepo;
    private array $tabchange;
    private array $tabpartner=[];
    private array $tabdate=[];
    private mixed $dateselct;
    private Post $potin;
    private Board $locatemedia;
    private PostRepository $postRepository;
    private PostEventAgendaSynchronizer $agendaSync;


    public function __construct(
        EntityManagerInterface $entityManager,
        BoardRepository $boardRepository,
        PostRepository $postRepository,
        PostEventRepository $postEventRepository,
        CalDateAppointement $calDate,
        PostEventAgendaSynchronizer $agendaSync,
    )
    {
        $this->em = $entityManager;
        $this->now = New DateTime();
        $this->calldate = $calDate;
        $this->repoevent=$postEventRepository;
        $this->boardrepo=$boardRepository;
        $this->postRepository=$postRepository;
        $this->agendaSync = $agendaSync;
    }

    protected function initEvent($data, $board): bool
    {
        $this->description=strip_tags($data['description'], null);
        $this->tabchange=$data['change'];
        $this->dateselct=json_decode($data['now'],true);
        $tempart=json_decode($data['partners'],true);
        $this->tabpartner=$tempart[0];
        $idpotin=$data['potin'];
        $this->potin=$this->postRepository->findOnePostById($idpotin);
        return true;
    }


    public function newEventPotin($data,$member, Board $board): array
    {
        if(!$this->initEvent($data, $board)) return MsgAjax::MSG_POST0;

        if($this->tabchange['edit']){
            $this->postevent=$this->repoevent->find($data['event']);
            if (!$this->postevent) return MsgAjax::MSG_ERR1;
            $this->postevent->setDatemajAt($this->now);
            $parution=$this->postevent->getAppointment();
            $oldlocate=$this->postevent->getLocatemedia();
            $cl_Tabdate=$parution->getTabdate();
        }else{
            $this->postevent = new PostEvent();
            $this->postevent->setKeymodule($board->getCodesite());
            $parution = new Appointments();
            $cl_Tabdate=new Tabdate();
            $this->postevent->setAuthor($member);
            $sector=new Sectors();
            $this->postevent->setSector($sector);
            $this->locatemedia=$this->boardrepo->find($this->tabpartner['id']);
            $this->locatemedia->addEvent($this->postevent);
            $oldlocate=null;
        }
        $this->potin->addEvent($this->postevent);
        $this->postevent->setTitre($this->potin->getTitre());
        $this->postevent->setDescription($this->description);

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

        if($this->tabchange['partner']['changPartner']){ //true changment de medialocate
            $oldlocate?->removeEvent($this->postevent);
            $this->locatemedia=$this->boardrepo->find($this->tabpartner['id']);
            $this->locatemedia->addEvent($this->postevent);
        }

        $gps=$this->locatemedia->getLocality();
        $this->postevent->setAppointment($this->calldate->initAppointEvent($this->now,$gps,$cl_Tabdate, $this->tabdate,$this->dateselct,$data['now'], $parution));
        $this->em->persist($this->postevent);
        $this->em->persist($board);
        $this->em->persist($this->locatemedia);
        $this->em->flush();

        $this->agendaSync->syncFromPostEvent($this->postevent);
        return MsgAjax::MSG_POSTOK;
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
            $this->agendaSync->syncFromPostEvent($el);
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
        $event->setSector(Null);
        $event->setAuthor(Null);
        $this->agendaSync->removeForPostEvent($event);
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
        $this->agendaSync->syncFromPostEvent($event);
        return MsgAjax::MSG_POSTOK;
    }

}
