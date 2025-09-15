<?php

namespace App\Service\Calendar;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;
use App\Calendar\Monthcalendar;
use \DateInterval;
use App\Entity\Marketplace;

Class InitCalendar
{


  private $security;

  public function __construct(Security $security)
    {
        $this->security = $security;
    }


    /*--- methode pour affichage des marchés -----*/

  public function inithebdoWithPeriod($month, $start, $end, $markets, $spef)
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
          $along=$period->getAlongPeriod();
          $periodEnd=$periodStart->add(new DateInterval('P0000-00-00T'.$along.''));
          // attention limité à + 24heures
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


  /*--- methode pour affichage des rendez-vous via le tableau des RdvClient-----METHODE UTILISEE*/

  public function initByRdv($month, $start, $end, $rdvs, $spef)
    {

      $daystab=[];
      $daystabvide=[];

      foreach ($rdvs as $rdv) {  // iteration des objets Periods inclus dans
                                          // le mois passé en argument de la methode
        $appoint=$rdv->getIdEvent();
        $duringappoint2=new DateInterval('P0000-00-00T'.$appoint->getAlongtime().'');

        $tabperiod= $appoint->getIdPeriods(); // normalement en rdv il n'ya qu'une periode

        foreach ($tabperiod as $period) {
          // on verifie si la periode commence dans l'interval du mois en argument
          $firstday=$period->getStartPeriod();
          $along=$period->getAlongPeriod();
          $periodEnd=$periodStart->add(new DateInterval('P0000-00-00T'.$along.''));
          // attention limité à + 24heures
          $lastday=$period->getAlongPeriod();
          $typechoice=$period->getPeriodeChoice();

          if($firstday >= $start && $firstday < $end){


            if($typechoice==0){       // si il y a pas de periodicité (normal dans un rendez-vous)
            $interval=$firstday->diff($lastday)->format('%a');
            // plage de validité de la periods en jour

              if($interval==0){  // ce ne serait pas normal dans le cas contraire pour un rendez-vous
                //on calcul la date du fin de rendez-vous ou on recupere le $lasday ???
                // todo faire que l'appoint s'arrete au dernier jour du mois $end
                // pour l'instan je fais rien idem si if=true  TODO
              $dateappoint=$firstday->format('Y-m-d');
              }else{  // le rendez-vous fait plus d'une journée
              $dateappoint=$firstday->format('Y-m-d');
              }

              if(!isset($daystab[$dateappoint])){
              $daystab[$dateappoint]=[$rdv];
              }else{
              $daystab[$dateappoint][]=$rdv; //TODO: creer un tableau pour identifier les evenements
              }

            }else{
            //une erreur
            }

          }// on passe à l'iteration suivance car pas dans la plage de date
        }
      }

      $tbmenurole=[];

      return ['date'=> $month, 'rdv' => $daystab, 'vide' => $daystabvide,
     'rang' => $tbmenurole, 'numrole' => $spef ];
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
        $lastday=$period->getAlongPeriod();

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
        $date->add(new DateInterval('P0000-00-00T'.$appoint->getAlongtime().''));
        // il faut maintenant calculer les dates à imputer au calendar en fonction des choix
        $typechoice=$period->getPeriodeChoice();

        if($typechoice!==0){       // si il y a une periodicité
          $numrepete=$period->getNumberrept();
          $numrepete=$numrepete>1 ? $numrepete : 1;
          $typerepete = $period->getTypeRept();

          $datedebutcalcul=$period->getStartPeriod();
          $datefindecalcul=$period->getAlongPeriod();
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




  public function inputapoint($daystab, $appoint, $dateappoint)
  {
        if (!isset($daystab[$dateappoint])) {
            $daystab[$dateappoint]=[$appoint];
            } else {
            $daystab[$dateappoint][]=$appoint; //TODO: creer un tableau pour identifier les evenements
            }
        return $daystab;
  }





public function menurole($month, $appoints, $spef)
    {

          switch ($spef) {
            case '0':
              $tbmenurole=array("all","gestion", "Commercial","Installation","sav");
              break;
            case '1':
              $tbmenurole=array("all","gestion", "Commercial","Installation","sav");
              break;
            case '2':
              $tbmenurole=array("all","Commercial","Installation","sav");
              break;
            case '3':
              $tbmenurole=array("all","Installation","sav");
              break;
            case '4':
              $tbmenurole=array("all","sav");
              break;
            case '7':
              $tbmenurole=array("all","banane");
              break;
            default:
               $tbmenurole=array();
              break;
          }

        return ['date'=> $month, 'appointments' => $daystab, 'vide' => $daystabvide, 'rang' => $tbmenurole,
        'numrole' => $spef ];
  }
/*



*/





}
