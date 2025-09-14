<?php


namespace App\Service\Modules;

use App\Repository\SubscriptionRepository;
use DateTime;


class Resator
{
    private SubscriptionRepository $subRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository)
    {
        $this->subRepository = $subscriptionRepository;
    }

    public function BuildTabDateEvent($event): array
    {
        $taballevents['event']=$event;
        $datenow =new DateTime();
        $tabdate=$event->getAppointment()->getTabdate()->getTabdatejso();
        foreach ($tabdate as $day){
            if($this->arrayNoEmpty($day)){
                foreach ($day as $date){
                    $tabday = explode(",", $date);
                    $tabday[1]=strval(intval($tabday[1])+1);
                    $t1=DateTime::createFromFormat('Y-m-d H:i:s',$tabday[0].'-'.$tabday[1].'-'.$tabday[2].' 00:00:00');
                    if($t1>=$datenow) $taballevents['date'][$t1->getTimestamp()]=[];
                }
            }
        }
        return $taballevents;


    }


    public function BuildTDateOfOneEvent($event): ?int
    {
        $datestamp=null;

        //$datenow = new DateTime();
        $tabdate = $event->getAppointment()->getTabdate()->getTabdatejso();
        foreach ($tabdate as $day) {
            if ($this->arrayNoEmpty($day)) {
                foreach ($day as $date) {
                    $tabday = explode(",", $date);
                    $tabday[1] = strval(intval($tabday[1]) + 1);
                    $t1 = DateTime::createFromFormat('Y-m-d H:i:s', $tabday[0] . '-' . $tabday[1] . '-' . $tabday[2] . ' 00:00:00');
                   // if ($t1 >= $datenow) $datestamp=$t1->getTimestamp();
                    $datestamp=$t1->getTimestamp();
                }
                break;
            }
        }
        return $datestamp;
    }

    public function CountSubByDateEvent($event): array
    {
        $allsub=$this->subRepository->findAllByEventId($event->getId());
        $tabarray['event']=$event;
        foreach ($allsub as $sub){
            $tabarray['sub'][$sub->getStarttime()->format('j/m/Y')][]=$sub;
        }
        return $tabarray;
    }

    public function calTabDateEvent($event): array
    {
        $taballevents['event']=$event;
        $tabdate=$event->getAppointment()->getTabdate()->getTabdatejso();  //recupère les date de l'event
        $allsub=$this->subRepository->findAllByEventId($event->getId());


        foreach ($tabdate as $day){
            if($this->arrayNoEmpty($day)){
                foreach ($day as $date){
                    $tabday = explode(",", $date);
                    $tabday[1]=strval(intval($tabday[1])+1);
                    $t1=DateTime::createFromFormat('Y-m-d H:i:s',$tabday[0].'-'.$tabday[1].'-'.$tabday[2].' 00:00:00');
                    $taballevents['date'][$t1->getTimestamp()]=[];
                }
            }
        }
        foreach ($allsub as $sub){
            $taballevents['date'][$sub->getStarttime()->getTimestamp()]=$sub; // ->format('j/m/Y')
        }
        return $taballevents;
    }

    public function resapotin($event): array
    {
        $taball=[];
        $taborder=[];

        $tabdate=$event->getAppointment()->getTabdate()->getTabdatejso();  //recupère les date de l'event
        foreach ($tabdate as $day){
            if($this->arrayNoEmpty($day)){
                foreach ($day as $date){
                    $tabday = explode(",", $date);

                    $tabday[1]=strval(intval($tabday[1])+1);

                    $t1=DateTime::createFromFormat('Y-m-d H:i:s',$tabday[0].'-'.$tabday[1].'-'.$tabday[2].' 00:00:00');
                    $taball[$t1->getTimestamp()]=$t1;
                }
            }
        }
        ksort($taball);  // tri le tableau key=timesatamp valeur=date

        foreach ($taball as $key => $orderdate){
           $taborder[$orderdate->format('j/m/Y')]= $orderdate;    // cree le tableau que des dates
        }
        return $taborder;
    }

    public function listDatesEvent($event): array
    {
        $taball=[];
        $taborder=[];

        $tabdate=$event['appointment']['tabdate']['tabdatejso'];  //recupère les date de l'event
        foreach ($tabdate as $day){
            if($this->arrayNoEmpty($day)){
                foreach ($day as $date){
                    $tabday = explode(",", $date);

                    $tabday[1]=strval(intval($tabday[1])+1);

                    $t1=DateTime::createFromFormat('Y-m-d H:i:s',$tabday[0].'-'.$tabday[1].'-'.$tabday[2].' 00:00:00');
                    $taball[$t1->getTimestamp()]=$t1;
                }
            }
        }
        ksort($taball);  // tri le tableau key=timesatamp valeur=date

        foreach ($taball as $key => $orderdate){
            $taborder[$orderdate->format('j/m/Y')]= $orderdate;    // cree le tableau que des dates
        }
        return $taborder;
    }

    public function newPreOrderPotin($preO, $customer,$req,$taborder){
        $tab=[];
        unset($req['save']);
        unset($req['_token']);
        // $req2=array_diff($req['inscription_potins'],['save','_token']);
        /*
        foreach ($req as $key=>$date) {
            $preO->setResaAt($taborder[$key]);
        }// todo table/collection pour plusieurs dates
        */

        foreach ($taborder as $dt ){
            $tab[]=$dt;
        }
        $preO->setResaAt($tab[$req['daychoice']]);
        return $preO;
    }

    public function newPreOrderMediaPotin($preO,$req,$date){ //todo pourquoi si compliqué ???
        $dateok=new DateTime();
        $dateok->setTimestamp($date);
        unset($req['save']);
        unset($req['_token']);
        $preO->setResaAt($dateok);
        return $preO;
    }

    function arrayNoEmpty($tab): bool
    {
        if (!empty($tab)){
            foreach ($tab as $valeur){
                if ($valeur !== null)
                    return true;
            }
        }
        return false;
    }

}