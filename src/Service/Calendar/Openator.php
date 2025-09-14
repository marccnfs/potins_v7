<?php


namespace App\Service\Calendar;


use App\Calendar\Monthcalendar;
use DateInterval;


class Openator
{
    /**
     * @var ReservationRepository
     */
    private $reservationRepository;
    /**
     * @var ProvidersRepository
     */
    private $repository;


    public function __construct(ReservationRepository $reservationRepository, ProvidersRepository $repository){
        $this->reservationRepository = $reservationRepository;
        $this->repository = $repository;
    }


    /**
     * @param $provider SpaceWebs
     * @param null $month
     * @param null $year
     * @return array
     * @throws \Exception
     *@var $month Monthcalendar
     */
    public function creaCalendarForNewOpenResaByProvider($id)
    {
        $moisok=null;
        $anneok=null;
        try{
            $month = new Monthcalendar($moisok, $anneok);
        }
        catch (\Exception $e){
            $month=new Monthcalendar();
        }
        $start=$month->getDebutmois();
        $end=$month->getFinmois()->add(new DateInterval('PT23H59M59S'));
        $provider = $this->repository->findopendaysForOneProvider($id); // query de tout les modules a revoir
        $calendar=$this->initByOpenDays($month, $provider);
        return $calendar;
    }

    /**
     * @param $month
     * @param $provider SpaceWebs
     * @param $start
     * @param $end
     * @param $daysopen Opendays
     * @return array
     */
   public function initByOpenDays($month, $provider)
    {
        $tabdays=[];
        $daysopen=$provider->getTabopendays();
        if($daysopen ==null)return ['date'=> $month, 'data' => $tabdays, 'state' => false]; // dans tous les cas

        // recuperer les days et leur statuts (int)
        $tabdays["d"]=str_split($daysopen->getWeekopen(),1);
        //d=[0,1,0,0,0,0,1,0] où lundi et samedi sont fermé (le dernier 0 est pas utilisé - dernier bit de l'octet pour js)

        //recuperer le tableau des horaires par jour
        $tabdays["tday"]=json_decode($daysopen->getOpentimeAllday());
        //ici on recupere depuis '{"08:00H5M00","13:00H5M00", etc...}' un tableau

        //recuperer le tableau des mois fermés
        $tabdays["closemonth"]=$daysopen->getClosedtabMonth();
        //ici on recupere depuis '{"8","9", etc...}' un tableau

        //recuperer le tableau des semaines fermées
        $tabdays["closeweek"]=$daysopen->getClosedtabWeek();
        //ici on recupere depuis '{"48","50", etc...}' un tableau

        //recuperer le tableau des des jour fermé ??? redondant ??
        $tabdays[]=$daysopen->getClosedtabday();
        //ici on recupere depuis '{"??", etc...}' un tableau

        //recuperer le tableau des dates fermées ponctuellement (une periode ou plusieurs)
        $tabdays["closedates"]=$daysopen->getClosedtabdate();
        //ici on recupere depuis '{"20190512-00:00:00/D20H4M3","20190512-00:00:00/D20H4M3", etc...}' un tableau

        //recuperer le tableau des dates exceptionnelement ouvert
        $tabdays["opendates"]=$daysopen->getOpenOnedate();
        //ici on recupere depuis '{"20190512-00:00:00/D20H4M3","20190512-00:00:00/D20H4M3", etc...}' un tableau


        return ['date'=> $month, 'data' => $tabdays, 'state' => true]; // renoyer quand edit/mis a jour du module reservation


    }




}