<?php


namespace App\Util;


use App\Entity\Agenda\Appointments;
use App\Entity\Agenda\CallbacksAppoint;
use App\Entity\Agenda\Periods;
use App\Entity\Agenda\Tabdate;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class CalDateAppointement
{
    private EntityManagerInterface $em;
    private DateTime $now;

    /**
     * Postar constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->now = New DateTime();

    }


    /**
     * @param $partner
     * @param $tabdate
     * @param $dateselect
     * @param $appointment Appointments
     * @return Appointments
     */
    public function initAppointEvent($now,$gps,$cl_Tabdate, $tabdate,$dateselect,$datanow,Appointments $appointment): Appointments
    {
        $appointment->setTypeAppointment(4);
        $start = current($tabdate);
        $end = end($tabdate);

        if (!$start instanceof DateTimeImmutable && $start !== false) {
            $start = DateTimeImmutable::createFromMutable($start);
        }
        if (!$end instanceof DateTimeImmutable && $end !== false) {
            $end = DateTimeImmutable::createFromMutable($end);
        }

        if (!$start instanceof DateTimeImmutable) {
            $start = (new DateTimeImmutable('now'))->setTime(0, 0, 0);
        }

        if (!$end instanceof DateTimeImmutable) {
            $end = $start;
        }

        $start = $start->setTime(0, 0, 0);
        $end = $end->setTime(23, 59, 59);

        if ($end <= $start) {
            $end = $start->modify('+1 day');
        }

        $appointment->setStarttime($start);
        $appointment->setEndtime($end);
        $appointment->setLocalisation($gps);
        $appointment->setStatut(true);
        $appointment->setConfirmed(true);
        $cl_Tabdate->setTabdatejso($dateselect);
        $cl_Tabdate->setTabdatestr($datanow);
        $appointment->setTabdate($cl_Tabdate);
        $appointment->setDatemajAt($now);
        return $appointment;
    }


    // reste en cours ........

    /**
     * @param $event
     * @param $start
     * @param $end
     * @param Appointments $appointment
     * @throws \Exception
     */
    public function appointEvent($event, $start, $end, Appointments $appointment)
    {
            $tepdate= $this->createdateformat($start);
            $caldate=$this->caltimealong($event['startminutes']);
            $start=$tepdate[0]->add(new DateInterval('PT'.$caldate[0].'H'.$caldate[1].'M'));
            $calalong=$this->caltimealong($event['alongminute']);
            $timealong=$calalong[0].':'.$calalong[1].':00'; //todo voir l'incidende lors de la lecture de la date sans les zeros
            $Instperiods=new Periods();

            $Instperiods->setPeriodeChoice(1);
            $Instperiods->setNumberrept(1);
            $Instperiods->setTypeRept(1);
            $Instperiods->setDaysweek($start->format('l'));
            $Instperiods->setStartPeriod($start);
            $Instperiods->setAlongPeriod($timealong);
            $this->em->persist($Instperiods);
            $appointment->addIdPeriod($Instperiods);

        return;

    }

    /**
     * @param $events
     * @param $em
     * @return mixed
     * @throws \Exception
     */
    public function addAppoint($events , $em)
    {
        $periodicity=1;
        $appointment = new Appointments();
        $appointment->setTypeAppointment(3);
        $tepdatefirst= $this->createdateformat($events[array_key_first($events)]['date']);
        $tepdatelast= $this->createdateformat($events[array_key_last($events)]['date']);
        if($periodicity==="2"){ //methode personnalisé
            $datestop=  $tepdatelast[1]->modify("+8 day");   //parametrage par defaut pour 8 jours//
        }else{
            $datestop = $tepdatelast[1];
        }
        $datedepart = $tepdatefirst[0];
        $appointment->setStarttime($datedepart);
        $appointment->setEndtime($datestop);
        $appointment->setStatut(true);
        $appointment->setConfirmed(true);
        $this->processEvent($appointment, $events, $em);

        return $appointment;
    }

    public function addPeriod($appointment, $form, $period=null)
    {

        $periodicity=$form->get("periodicity")->getData(); //la peridodicité est indiqué par l'onglet (js)
        $numrepete=$form->get("numberrepete")->getData();

        if($periodicity==="2"){     //methode personnalisé
            $typerepete=$form->get("typerepete")->getData();
            $numrepete=$numrepete>1 ? $numrepete : 1;
            $daysweek=implode("-",$form->get("daysweek")->getData());
            $durestart=$form->get('heuredebut')->getData();
            $dureend=$form->get('heurefin')->getData();
            //$daymobth=$form->get("daymonth")->getData();
        }else{
            $typerepete=$periodicity;
            $numrepete=1;
            $datedepart = $this->convertorDateStringStart($form);
            $daysweek=$datedepart->format('l');

            $daymobth=$datedepart->format('l');
            $durestart=$form->get('heuredebutone')->getData();
            $dureend=$form->get('heurefinone')->getData();
        }

        if(!isset($period)){
            $Instperiods=new Periods();
        }
        else{
            $Instperiods=$period;
        }

        $Instperiods->setPeriodeChoice(intval($periodicity));
        $Instperiods->setNumberrept($numrepete);
        $Instperiods->setTypeRept($typerepete);
        $Instperiods->setDaysweek($daysweek);
        //$Instperiods->setDaymonth($daymobth);
        $Instperiods->setStartPeriod($durestart);
        $Instperiods->setAlongPeriod($dureend->diff($durestart)->format('%H:%I:%S'));
        $appointment->addIdPeriod($Instperiods);

        return $Instperiods;

    }

    public function caltimealong($minut){
        $h=strval($minut/60);
        $i=strval($minut%60);
        return[$h,$i];
    }

    protected function createdateformat($stringdate){
        $tabdate=explode(',',$stringdate);
        $t1=DateTime::createFromFormat('Y-m-d H:i:s',$tabdate[0].'-'.$tabdate[1].'-'.$tabdate[2].' 00:00:00');
        $t2=DateTime::createFromFormat('Y-m-d H:i:s',$tabdate[0].'-'.$tabdate[1].'-'.$tabdate[2].' 23:59:59');
        return $tepdate=[$t1,$t2];
    }
    protected function createOnedateformat($stringdate){
        if($stringdate!=""){
            $tabdate=explode(',',$stringdate);
            return DateTime::createFromFormat('Y-m-d H:i:s',$tabdate[0].'-'.$tabdate[1].'-'.$tabdate[2].' 00:00:00');
        }else{
            return new DateTime();
        }
    }

    /**
     * @param $listdates
     * @param $entity
     * @return Appointments
     */
    public function daysParutions($listdates, $entity, $board): Appointments
    {
        $tabdates=explode(';',$listdates);
        if($c=count($tabdates)<1){
            $tabdates[0]=new DateTime();
        }else {
            foreach ($tabdates as $key => $date) {
                $tabdates[$key] = $this->createOnedateformat($date);
            }
        }
        sort($tabdates);
        $appointment = new Appointments();
        $appointment->setTypeAppointment(4);
        $appointment->setStarttime($tabdates[0]);
        $appointment->setEndtime(end($tabdates));
        $appointment->setLocalisation($board->getLocality()[0]);
        $entity->setEndAt($appointment->getEndtime());
        $appointment->setStatut(true);
        $appointment->setConfirmed(true);
        return $appointment;
    }

    /**
     * @param $website
     * @param $form
     * @param $appointment Appointments
     * @return Appointments
     */
    public function alongDaysFormule($website, $form, Appointments $appointment)
    {
        $start=$form->get('start')->getData();
        $end=$form->get('end')->getData();
        $appointment->setTypeAppointment(4);
        $appointment->setStarttime($start);
        $appointment->setEndtime($end);
        $appointment->setLocalisation($website->getLocality()[0]);
        $appointment->setStatut(true);
        $appointment->setConfirmed(true);
        return $appointment;
    }

    /**
     * @param $partner
     * @param $start
     * @param $end
     * @param $appointment Appointments
     * @return Appointments
     */
    public function alongDayEvent($partner, $start, $end, Appointments $appointment): Appointments
    {
        $appointment->setTypeAppointment(4);
        $appointment->setStarttime($start);
        $appointment->setEndtime($end);
        $appointment->setLocalisation($partner->getLocality()[0]);
        $appointment->setStatut(true);
        $appointment->setConfirmed(true);
        return $appointment;
    }

    /**
     * @param $website
     * @param $appointment Appointments
     * @return Appointments
     */
    public function alongDaysNow($website, Appointments $appointment)
    {
        $start=new DateTime();
        $end=(new DateTime())->modify('+1 month');
        $appointment->setTypeAppointment(4);
        $appointment->setStarttime($start);
        $appointment->setEndtime($end);
        $appointment->setLocalisation($website->getLocality()[0]);
        $appointment->setStatut(true);
        $appointment->setConfirmed(true);
        return $appointment;
    }


    /**
     * @param Appointments $appointment
     * @param $events
     * @param $em EntityManagerInterface
     * @throws \Exception
     */
    public function initperdiod($appointment, $tabdates)
    {
        foreach ($events as $event){
            $tepdate= $this->createdateformat($event['date']);
            $caldate=$this->caltimealong($event['startminutes']);
            $start=$tepdate[0]->add(new DateInterval('PT'.$caldate[0].'H'.$caldate[1].'M'));
            $calalong=$this->caltimealong($event['alongminute']);
            $timealong=$calalong[0].':'.$calalong[1].':00'; //todo voir l'incidende lors de la lecture de la date sans les zeros
            $Instperiods=new Periods();
            $Instperiods->setPeriodeChoice(1);
            $Instperiods->setNumberrept(1);
            $Instperiods->setTypeRept(1);
            $Instperiods->setDaysweek($start->format('l'));
            $Instperiods->setStartPeriod($start);
            $Instperiods->setAlongPeriod($timealong);
            $em->persist($Instperiods);
            $appointment->addIdPeriod($Instperiods);
        }
        return;

    }

    public function addCallBack($appointment, $callbackappoint)
    {
        $instCallbacksAppoint=new CallbacksAppoint();
        $instCallbacksAppoint->setChoiceCallback($callbackappoint);
        $appointment->addFrequenceCallback($instCallbacksAppoint);

        return $instCallbacksAppoint  ;

    }




    public function convertorDateStringPeridoStart($date)
    {
        $okdate = DateTime::createFromFormat('Y-m-d H:i:s', $date->format('Y-m-d'));
        return $okdate;
    }

    public function convertorDateStringPeriodEnd($form)
    {
        return DateTime::createFromFormat('Y-m-d H:i:s', ($form->get('dateEndPeriod')->getData()->format('Y-m-d')).' '.($form->get('heurefin')->getData()->format('H:i:s')));
    }

    public function convertorDateStringStart($form)
    {
        $okdate = DateTime::createFromFormat('Y-m-d H:i:s', ($form->get('dateStartPeriodone')->getData()->format('Y-m-d')).' '.($form->get('heuredebutone')->getData()->format('H:i:s')));
        return $okdate;
    }

    public function convertorDateStringEnd($form)
    {
        return DateTime::createFromFormat('Y-m-d H:i:s', ($form->get('dateStartPeriodone')->getData()->format('Y-m-d')).' '.($form->get('heurefinone')->getData()->format('H:i:s')));
    }



    public function initfinalong($appoint)
    {
        return $appoint->getStartime()->add( new DateInterval('P0000-00-00T'.$appoint->getAlongtime().''));
    }

    public function initdebutperiod($startPeriod)
    {
        return DateTime::createFromFormat('Y-m-d H:i:s' , $startPeriod);
    }
    public function initfinperiod($endPeriod)
    {
        return DateTime::createFromFormat('Y-m-d H:i:s' , $endPeriod);
    }

    public function initDatedebut($datedebut)
    {
        return DateTime::createFromFormat('Y-m-d' , $datedebut);
    }
    public function initHeuredebut($heuredebut)
    {
        return DateTime::createFromFormat('H:i:s' , $heuredebut);
    }
    public function initDatefin($datefin)
    {
        return DateTime::createFromFormat('Y-m-d' ,$datefin);
    }
    public function initHeurefin($heurefin)
    {
        return DateTime::createFromFormat('H:i:s' ,$heurefin);
    }

    public function editPost($appointment, $periods, $form) //todo totalement
    {
        $period=$periods[0]; // pour l'instant on ne gere qu'une seule periode

        $finstring=($period->getStartPeriod())->format('Y-m-d H:i:s');
        $periodEnd=new DateTime($finstring);
        $along=$period->getAlongPeriod();
        $periodEnd->add(new DateInterval('P0000-00-00T'.$along.''));
        $periodStart=$period->getStartPeriod();

        if($period->getPeriodeChoice()==1){
            $form->get('dateStartPeriodone')->setData($appointment->getStarttime());
            $form->get('heuredebutone')->setData($periodStart);
            $form->get('heurefinone')->setData($periodEnd);
        }else{
            $form->get('dateStartPeriod')->setData($appointment->getStarttime());
            $form->get('heuredebut')->setData($periodStart);
            $form->get('heurefin')->setData($periodEnd);
        }

        $form->get('dateEndPeriod')->setData($appointment->getEndtime());
        $form->get("numberrepete")->setData($period->getNumberrept());
        $form->get("typerepete")->setData($period->getTypeRept());
        $form->get("periodicity")->setData($period->getPeriodeChoice());
        $form->get("typerepete")->setData($period->getTypeRept());
        $form->get("daysweek")->setData(explode("-",$period->getDaysweek()));
        //$form->get("callback")->setData(0); // desactivé en version simple
        //$form->get("daymonth")->setData(1);

        return $form;
    }


    public function  majPeriodPost($periods, $form) //todo totalement
    {
        $period=$periods[0];  //pour l'instant une seule periode gérée

        $periodicity=$form->get("periodicity")->getData(); //la peridodicité est indiqué par l'onglet (js)
        $numrepete=$form->get("numberrepete")->getData();

        if($periodicity==="2"){     //methode personnalisé
            $typerepete=$form->get("typerepete")->getData();
            $numrepete=$numrepete>1 ? $numrepete : 1;
            $daysweek=implode("-",$form->get("daysweek")->getData());
            $durestart=$form->get('heuredebut')->getData();
            $dureend=$form->get('heurefin')->getData();
            //$daymobth=$form->get("daymonth")->getData();
        }else{
            $typerepete=$periodicity;
            $numrepete=1;
            $datedepart = $this->convertorDateStringStart($form);
            $daysweek=$datedepart->format('l');

            $daymobth=$datedepart->format('l');
            $durestart=$form->get('heuredebutone')->getData();
            $dureend=$form->get('heurefinone')->getData();
        }

        $period->setPeriodeChoice(intval($periodicity));
        $period->setNumberrept($numrepete);
        $period->setTypeRept($typerepete);
        $period->setDaysweek($daysweek);
        //$Instperiods->setDaymonth($daymobth);
        $period->setStartPeriod($durestart);
        $period->setAlongPeriod($dureend->diff($durestart)->format('%H:%I:%S'));

        return $period;
    }


    public function  majAppointPost($appointment, $form) //todo totalement
    {
        $periodicity=$form->get("periodicity")->getData();

        if($periodicity==="2"){ //methode personnalisé
            if($form->get("alongchoice")->getData()===0){  //parametrage pour toujours (on limite a deux ans)
                $datedepart = $this->convertorDateStringPeridoStart($form);
                $datestop= $this->convertorDateStringPeridoStart($form);
                $datestop->modify("+2 year");
            }

            if($form->get("alongchoice")->getData()===1){
                $datedepart = $this->convertorDateStringPeridoStart($form);
                $datestop= ($this->convertorDateStringPeriodEnd($form))->modify("+1 day");
            }
        }else{

            $datedepart = $this->convertorDateStringStart($form);
            $datestop = $this->convertorDateStringEnd($form);
        }

        $appointment->setStarttime($datedepart);
        $appointment->setEndtime($datestop);
        $appointment->setStatut(true);
        $appointment->setConfirmed(true);
        $appointment->setName($form->get('name')->getData());

        return $appointment;
    }

}
