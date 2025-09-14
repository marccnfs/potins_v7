<?php

namespace App\Service\Calendar;


use App\Calendar\Monthcalendar;
use App\Calendar\WeekCalendar;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;




Class InitMarket
{


  private $security;
  private $marketRepository;

  public function __construct(Security $security, MarketationRepository $marketRepository)
    {
        $this->security = $security;
        $this->marketRepository = $marketRepository;
    }



    public function initOneMarketByMonth($id, $month=null, $year=null)
    {
        try{
            $month = new Monthcalendar($month, $year);
        }
        catch (\Exception $e){
            $month=new Monthcalendar();
        }
        $start=$month->getDebutmois();
        $end=$month->getFinmois();
        $tabMarket=$this->marketRepository->findOneMarketModuleWhitAppointAndPeriod($id, $start, $end);
        if (null === $tabMarket) {
            throw new NotFoundHttpException("aucun evenement enregistré");
        }
        return $this->listDaysMarketByMonth($month, $start, $end, $tabMarket);
    }


    /**
     * @param $market Eventation
     * @param null $week
     * @return array
     * @throws \Exception
     */
    public function initAllMarketsThanOneProviderByWeek($provider, $week=null) //todo n'est plus valid market ->module
    {
            $weekentity = new WeekCalendar($week); 
            $start=$weekentity->getDebutweek();
            $end=$weekentity->getFinweek();
            $markets=$this->marketRepository->findMarketByAppointWithPeriod($provider['id'], $start, $end);
            if (null === $markets) {
            throw new NotFoundHttpException("aucun evenement enregistré");
                $markets=[];
            }
            return $this->listMarketByWeek($weekentity, $markets);
  }

    public function initOneMarketByWeek($provider, $week=null) //todo n'est plus valid market ->module
    {
        $weekentity = new WeekCalendar($week);
        $start=$weekentity->getDebutweek();
        $end=$weekentity->getFinweek();
        $markets=$this->marketRepository->findMarketByAppointWithPeriod($market['id'], $start, $end);
        if (null === $markets) {
            throw new NotFoundHttpException("aucun evenement enregistré");
            $markets=[];
        }
        return $this->listMarketByWeek($weekentity, $markets);
    }


    /**
     * @param $market
     * @param null $month
     * @param null $year
     * @return array
     * @throws \Exception
     */
    public function initAllMarketsThanOneProviderByMonth($provider, $module, $month=null, $year=null)
    {
            try{
            $month = new Monthcalendar($month, $year); 
            }
            catch (\Exception $e){
              $month=new Monthcalendar();
            }
            $start=$month->getDebutmois();
            $end=$month->getFinmois();
            $daysmarkets=$this->marketRepository->findMarketByAppointWithPeriod($market['id'], $start, $end);
            if (null === $daysmarkets) {
              throw new NotFoundHttpException("aucun evenement enregistré");
              $markets=[];
            }

            return $this->listMarketByMonth($month, $start, $end, $markets);

  }



 
      public function listMarketByWeek($week, $markets)
        {

          $daystab=[];
          $daystabvide=[];
          $tabmarkets=[];
          $i=0;

          $start=$week->getDebutweek();
          $end=$week->getFinweek();


          foreach ($markets as $market) {  // iteration des objets markets inclus dans
                                              // le mois passé en argument de la methode
            $appoint=$market->getAppointment();
            $startappoint=$appoint->getStarttime();
            $endappoint=$appoint->getEndtime();

            $periods=$appoint->getIdPeriods();

            foreach ($periods as $period){

              $typechoice=$period->getPeriodeChoice();

              if($typechoice!==1){
                    // si il y a une periodicité
                $numrepete=$period->getNumberrept();
                $numrepete=$numrepete>1 ? $numrepete : 1;
                $typerepete=$period->getTyperept();
                $interval=$startappoint->diff($endappoint)->format('%a'); // plage de validité de la periods en jour

                switch ($typerepete) {
                  case '1': //jour

                  $dayweek=$start;
                  $endtest= new \DateTimeimmutable($end->format('Y-m-d').'+1 day'); // a revoir exactement

                  if($interval>1){

                    while ($dayweek < $endtest) {
                      if($dayweek >= $start && $dayweek <= $end){

                      $day=$dayweek->format('l');
                      $tabmarkets[$i][$day]=$market;
                      }
                      $dayweek=$dayweek->modify('+'.$numrepete.'day');
                    }
                  }else{
                    if($dayweek >= $start && $dayweek < $end){
                    $day=$dayweek->format('l');
                    $tabmarkets[$i][$day]=$market;
                    }
                  }
                  break;

                  case '2':  // semaine
                      $daysweek=explode("-", $period->getDaysweek());
                      $dateday=$startappoint;
                      $lastday=$endappoint;

                      $dayweek=$start;

                          foreach ($daysweek as $day) {  //ici on cherche les jour renseigné
                          $dayappoint=$dayweek->modify($day);
                          if($dayappoint >= $start && $dayappoint <= $end){
                            $tabmarkets[$i][$day]=$market;
                            }
                          }

                      break;

                  case '3':  // periodicité en mois a faire
                       $daystab=$this->imputtabmonth($daystab, $interval, $numrepete, $start, $end, $startappoint, $market);
                  break;
                  }

              }else{    // si pas de periodicité cas d'un evenement ponctuel
                $day=$startappoint->format('l');

                $tabmarkets[$i][$day]=$market;;
              }
          }// fin du for periode

          $i++;
        } // fin du for sur market



        return ['date'=> $week, 'markets' => $tabmarkets, 'vide' => $daystabvide];
      }


    /**
     * @param $month
     * @param $start
     * @param $end
     * @param $tabMarket
     * @return array
     */
    public function listDaysMarketByMonth($month, $start, $end, $tabMarket)
    {
      $daystab=[];
      foreach ($tabMarket as $market) {  // iteration du array market (un seul element si recheche sur one market)
        $appoint=$market['idmodule']['appointment'];
        $startappoint=$appoint['starttime'];
        $endappoint=$appoint['endtime'];
        $periods=$appoint['idPeriods'];
          foreach ($periods as $period){
          $typechoice=$period['periodeChoice'];
          if($typechoice!==1){       // si il y a une periodicité
            $numrepete=$period['numberrept'];
            $numrepete=$numrepete>1 ? $numrepete : 1; 
            $typerepete=$period['typerept'];
            $daysweek=explode("-", $period['daysweek']);
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
                  $daystab=$this->inputapoint($daystab, $market, $dateappoint);
                  $firstday=$firstday->modify('+'.$numrepete.'day');
                  }else{
                  $firstday=$firstday->modify('+'.$numrepete.'day');
                  }
                }
              }else{
                if($firstday >= $start && $firstday <= $end){
                $dateappoint=$firstday->format('Y-m-d');
                $daystab=$this->inputapoint($daystab, $market, $dateappoint);
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
                        $daystab=$this->inputapoint($daystab, $market, $dateappoint);
                        }   
                      }                                                       
                    $jourmois=$jourmois->modify('+'.$numrepete.'week'); 
                  }                                
                  break;

              case '3':  // periodicité en mois // todo la periodicité en mois et année
                   $daystab=$this->imputtabmonth($daystab, $interval, $numrepete, $start, $end, $startappoint, $market);               
              break;
              }

          }else{    // si pas de periodicité cas d'un evenement ponctuel
            $dateappoint=$appoint->getStarttime()->format('Y-m-d');
            $daystab=$this->inputapoint($daystab, $market, $dateappoint);
          } 
      }// fin du for periode
    } // fin du for sur market

    return ['date'=> $month, 'daysmarkets' => $daystab, 'marketation'=>$tabMarket];
}  

 

  public function inputapoint($daystab, $market, $dateappoint)
  {
        if (!isset($daystab[$dateappoint])) {
            $daystab[$dateappoint]=[$market];
            } else {
            $daystab[$dateappoint][]=$market; //TODO: creer un tableau pour identifier les evenements
            }
        return $daystab;
  }



	
}