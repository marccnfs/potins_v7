<?php


namespace App\Service\Calendar;

use App\Calendar\Monthcalendar;
use App\Entity\Agenda\Appointments;
use App\Entity\Posts\Post;
use App\Repository\PostRepository;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class Calendator
{

    private $em;
    /**
     * @var PostRepository
     */
    private PostRepository $postationRepository;


    public function __construct(PostRepository $postationRepository, EntityManagerInterface $em){
        $this->em = $em;
        $this->postationRepository = $postationRepository;
    }


    /**
     * @param $provider
     * @param null $month
     * @param null $year
     * @return array
     * @throws \Exception
     */
    public function calendarPostByProvider($provider, $month=null, $year=null)
    {
        $moisok=intval($month);
        $anneok=intval($year);
        try{
            $month = new Monthcalendar($moisok, $anneok);
        }
        catch (\Exception $e){
            $month=new Monthcalendar();
        }
        $start=$month->getDebutmois();
        $end=$month->getFinmois()->add(new DateInterval('PT23H59M59S'));

        $posts = $this->postationRepository->findPostByAppointWithPeriodForOneTeam($provider, $start, $end); // query de tout les modules a revoir
        if (null === $posts) {
            //throw new NotFoundHttpException("aucun evenement enregistré");
            $posts=[];
        }
        // appel la methode d'initialisation du calendrier
        $calendar=$this->initByPost($month, $start, $end, $posts);
        return $calendar;
    }

    /**
     * @param $month
     * @param $start
     * @param $end DateTimeImmutable
     * @param $posts
     * @var $startappoint DateTime
     * @var $appoint Appointments
     * @return array
     */
    public function initByPost($month, $start, $end, $posts): array
    {
        $daystab=[];
        $daystabvide=[];
        $tabpost=[];

        /** @var $post  Post*/
        foreach ($posts as $post) {  // iteration des objets Periods inclus dans
            // le mois passé en argument de la methode
            $appoint=$post->getIdmodule()->getAppointment();
            $startappoint=$appoint->getStarttime();
            $endappoint=$appoint->getEndtime();
            $periods=$appoint->getIdPeriods();

            //$duringappoint2=new DateInterval('P0000-00-00T'.$appoint->getAlongtime().'');

            foreach ($periods as $period) {

                $typechoice=$period->getPeriodeChoice();

                if($typechoice!==1){       // si il y a une periodicité
                    $numrepete=$period->getNumberrept();
                    $numrepete=$numrepete>1 ? $numrepete : 1;
                    $typerepete = $period->getTypeRept();

                    //$startappoint=$period->getStartPeriod();
                    //$along=$period->getAlongPeriod();
                    //$endappoint=$periodStart->add(new DateInterval('P0000-00-00T'.$along.''));

                    $daysweek=explode("-", $period->getDaysweek());

                    $interval=$startappoint->diff($endappoint)->format('%a'); // plage de validité de la periods en jour

                    switch ($typerepete) {       // periodicité par jour
                        case '1':
                            $firstday=$startappoint;
                            $endweekmonth=$end->modify('last day of this month');
                            $endsunday=$endweekmonth->modify('Sunday');
                            if($interval>1){

                                while ($firstday <= $endsunday->modify('+1 day')) {
                                    if($firstday >= $start && $firstday < $endappoint){
                                        $dateappoint=$firstday->format('Y-m-d');
                                        $daystab=$this->inputapoint($daystab, $post, $dateappoint);
                                        $firstday=$firstday->modify('+'.$numrepete.'day');
                                    }else{
                                        $firstday=$firstday->modify('+'.$numrepete.'day');
                                    }
                                }

                            }else{
                                if($firstday >= $start && $firstday <= $end){
                                    $dateappoint=$firstday->format('Y-m-d');
                                    $daystab=$this->inputapoint($daystab, $post, $dateappoint);
                                }
                            }
                            break;

                        case '2':  // periodicité en semaine
                            $firstday=$startappoint;
                            $lastday=$endappoint;
                            //1er lundi du calendier (mois ou mois-1)
                            $jourmois=$start->modify('Monday this week');
                            while ($jourmois < $end) {  // on boucle sur le mois

                                if($jourmois >= $firstday && $lastday >= $jourmois){ //si le debut appoint est egale/sup au jour du mois
                                    foreach ($daysweek as $day) {  //ici on cherche les jour renseigné
                                        $dayappoint=$jourmois->modify($day);
                                        $dateappoint=$dayappoint->format('Y-m-d');
                                        $daystab=$this->inputapoint($daystab, $post, $dateappoint);
                                    }
                                }
                                $jourmois=$jourmois->modify('+'.$numrepete.'week');
                            }
                            break;

                        case '3':  // periodicité en mois //TODO
                            $daystab=$this->imputtabmonth($daystab, $interval, $numrepete, $start, $end, $startappoint, $post);
                            break;
                    }

                }else{    // si pas de periodicité cas d'un evenement ponctuel
                    $dateappoint=$appoint->getStarttime()->format('Y-m-d');
                    $daystab=$this->inputapoint($daystab, $post, $dateappoint);
                }
            }// fin du for periode
        } // fin du for sur post

        return ['date'=> $month, 'posts' => $daystab, 'vide' => $daystabvide];
    }



    public function inputapoint($daystab, $post, $dateappoint): array
    {
        if (!isset($daystab[$dateappoint])) {
            $daystab[$dateappoint]=[$post];
        } else {
            $daystab[$dateappoint][]=$post; //TODO: creer un tableau pour identifier les evenements
        }
        return $daystab;
    }


    public function inithebdoWithPeriod($month, $start, $end, $markets)
    {
        $daystab=[];
        $daystabvide=[];
        $tabmarkets=[];
        $i=0;
        foreach ($markets as $market) {
            // prevoir un controle pour savoir si ùmarkets n'est pas nul
            $appoint=$market->getIdEvent();
            $tabperiod= $appoint->getIdPeriods();

            foreach ($tabperiod as $period) {
                $periodStart=$period->getStartPeriod();
                $periodEnd=$period->getEndPeriod();
                if($periodEnd >= $start && $periodStart < $periodEnd){
                    $daysmarket=explode("-", $period->getDaysweek());

                    foreach ($daysmarket as $daymarket) {
                        $tabmarkets[$i][$daymarket]=$market;
                    }
                }
            }
            $i++;
        }
        $tbmenurole=[];
        return ['date'=> $month, 'markets' => $tabmarkets ];
    }





    /*--- methode pour affichage des rendez-vous a partir d'un tableau des periods-----*/

    public function initRdvViaPeriod($month, $start, $end, $periods, $spef)
    {

        $daystab=[];
        $daystabvide=[];

        foreach ($periods as $period) {  // iteration des objets Periods inclus dans
            // le mois passé en argument de la methode
            $appoint=$period->getidAppointment();
            $duringappoint2=new DateInterval('P0000-00-00T'.$appoint->getAlongtime().'');

            // on verifie si la periode est dans l'interval du mois en argument
            $firstday=$period->getStartPeriod();
            $lastday=$period->getEndPeriod();

            if($firstday >= $start && $firstday < $end)
            {


                // il faut maintenant calculer les dates à imputer au calendar en fonction des choix
                $typechoice=$period->getPeriodeChoice();

                if($typechoice==0){       // si il y a pas de periodicité (normal dans un rendez-vous)
                    $interval=$firstday->diff($lastday)->format('%a');
                    // plage de validité de la periods en jour
                    if($interval==0)// ce ne serait pas normal dans le cas contraire pour un rendez-vous
                    {//on calcul la date du fin de rendez-vous ou on recupere le $lasday ???
                        // todo faire que l'appoint s'arrete au dernier jour du mois $end

                        // pour l'instan je fais rien idem si if=true  TODO

                        $dateappoint=$firstday->format('Y-m-d');

                    }else{  // le rendez-vous fait plus d'une journée

                        $dateappoint=$firstday->format('Y-m-d');
                    }

                    if (!isset($daystab[$dateappoint])) {
                        $daystab[$dateappoint]=[$appoint];
                    } else {
                        $daystab[$dateappoint][]=$appoint; //TODO: creer un tableau pour identifier les evenements
                    }


                }else{
                    //une erreur
                }
            }
        }

        $tbmenurole=[];

        return ['date'=> $month, 'appointments' => $daystab, 'vide' => $daystabvide,
            'rang' => $tbmenurole, 'numrole' => $spef ];
    }


// calendrier general


    public function initWithPeriod($month, $start, $end, $periods, $spef)
    {

        $daystab=[];
        $daystabvide=[];

        foreach ($periods as $period) {  // iteration des objets Periods inclus dans
            // le mois passé en argument de la methode
            $appoint=$period->getidAppointment();

            // version via un array a mettre en place si plus de 25 h sinon erreur
            //list($heure, $minutes, $secondes)=explode(":",$appoint->getAlongtime());
            //$duringappoint=new DateInterval('PT'.$heure.'H'.$minutes.'M'.$secondes.'S');

            // on peut aussi faire une version direct (limite à 25 h sinon erreur) avec le format de chaine 00:00:00
            $duringappoint2=new DateInterval('P0000-00-00T'.$appoint->getAlongtime().'');

            // il faut maintenant calculer les dates à imputer au calendar en fonction des choix
            $typechoice=$period->getPeriodeChoice();

            if($typechoice!==0){       // si il y a une periodicité
                $numrepete=$period->getNumberrept();
                $numrepete=$numrepete>1 ? $numrepete : 1;
                $typerepete = $period->getTypeRept();

                $datedebutcalcul=$period->getStartPeriod();
                $datefindecalcul=$period->getEndPeriod();
                $daysweek=explode("-", $period->getDaysweek());

                $interval=$datedebutcalcul->diff($datefindecalcul)->format('%a'); // plage de validité de la periods en jour

                switch ($typerepete) {       // periodicité par jour
                    case '1':
                        $firstday=$datedebutcalcul;
                        $endweekmonth=$end->modify('last day of this month');
                        $endsunday=$endweekmonth->modify('Sunday');
                        if($interval>1){

                            while ($firstday <= $endsunday->modify('+1 day')) {
                                if($firstday >= $start && $firstday < $datefindecalcul){
                                    $dateappoint=$firstday->format('Y-m-d');
                                    $daystab=$this->inputapoint($daystab, $appoint, $dateappoint);
                                    $firstday=$firstday->modify('+'.$numrepete.'day');
                                }else{
                                    $firstday=$firstday->modify('+'.$numrepete.'day');
                                }
                            }

                        }else{
                            if($firstday >= $start && $firstday <= $end){
                                $dateappoint=$firstday->format('Y-m-d');
                                $daystab=$this->inputapoint($daystab, $appoint, $dateappoint);
                            }
                        }
                        break;

                    case '2':  // periodicité en semaine
                        $firstday=$datedebutcalcul;
                        $lastday=$datefindecalcul;

                        //$intervalweek=$start->diff($end)->format('%a');
                        //$nbtour=ceil(($interval/7)/$numrepete);
                        $newdateappoint=$start;

                        // correctif car boucle infino----------------------------------------------------------


                        while ($newdateappoint < $end) {

                            if($lastday >= $start && $firstday <= $end){

                                $week=$newdateappoint->modify('Monday this week');
                                foreach ($daysweek as $day) {
                                    $dayappoint=$week->modify($day);
                                    $dateappoint=$dayappoint->format('Y-m-d');
                                    $daystab=$this->inputapoint($daystab, $appoint, $dateappoint);
                                }
                            }
                            $newdateappoint=$newdateappoint->modify('+'.$numrepete.'week');

                        }
                        break;

                    case '3':  // periodicité en mois
                        $daystab=$this->imputtabmonth($daystab, $interval, $numrepete, $start, $end, $datedebutcalcul, $appoint);
                        break;
                }

            }else{    // si pas de periodicité cas d'un evenement ponctuel
                $dateappoint=$period->getStartPeriod()->format('Y-m-d');
                $daystab=$this->inputapoint($daystab, $appoint, $dateappoint);
            }
        }

        $tbmenurole=[];

        return ['date'=> $month, 'appointments' => $daystab, 'vide' => $daystabvide,
            'rang' => $tbmenurole, 'numrole' => $spef ];
    }


}
