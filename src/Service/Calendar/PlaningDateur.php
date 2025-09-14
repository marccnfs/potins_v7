<?php


namespace App\Service\Calendar;

use \DateTime;
use App\Entity\Agenda\Periods;
use App\Entity\Agenda\CallbacksAppoint;
use \DateInterval;

Class PlaningDateur
{
      

    public function initDates($form, $addate=null)
    {             
                $timenow = new DateTime();
                
                if(!isset($addate)){    
                 // pas de date en get
                 
                $form->get('datedebut')->setData($timenow); //$this->initDatedebut($datenow->format('Y-m-d'));
                $form->get('heuredebut')->setData($timenow); //$this->initHeuredebut($timenow->format('H:i:s'));
                $form->get('datefin')->setData( $timenow); //$this->initDatefin($datenow->format('Y-m-d'));
                $form->get('heurefin')->setData($timenow); //$this->initHeurefin($timenow->format('H:i:s'));

                }else{   // on passe une date en get

                $form->get('datedebut')->setData($this->initDatedebut($addate));
                $form->get('heuredebut')->setData($this->initHeuredebut($timenow->format('H:i:s')));
                $form->get('datefin')->setData($this->initDatefin($addate));
                $form->get('heurefin')->setData($this->initHeurefin($timenow->format('H:i:s')));

                }

                return $form;
        }

        public function initTimes($form,$appointment=null, $period=null,  $addate=null)
        {
                
                $timenow = new DateTime();
                
                if(!isset($addate)){    
                 // pas de date en get
                 
                $form->get('dateStartPeriod')->setData($timenow); //$this->initDatedebut($datenow->format('Y-m-d'));
                $form->get('heuredebut')->setData($timenow); //$this->initHeuredebut($timenow->format('H:i:s'));
                $form->get('dateEndPeriod')->setData( $timenow); //$this->initDatefin($datenow->format('Y-m-d'));
                $form->get('heurefin')->setData($timenow); //$this->initHeurefin($timenow->format('H:i:s'));

                }else{   // on passe une date en get

                $form->get('dateStartPeriod')->setData($period->getStartPeriod()); //initdebutperiod($addate));
                $form->get('heuredebut')->setData($appointment->getStartime()); //$this->initdebutalong($along->format('H:i:s')));
                $form->get('dateEndPeriod')->setData($period->getEndPeriod()); //$this->initfinperiod($addate));
                $form->get('heurefin')->setData($this->initfinalong($appointment)); //$this->initfinalong($along)->format('H:i:s')));

                }

                return $form;
        }

        public function initMarket($form, $appointment=null, $period=null,  $addate=null)
        {
                
                $timenow = new DateTime();
                
                if(!isset($addate)){    
                 // pas de date en get
                 
                //$form->get('dateStartPeriod')->setData($timenow); //$this->initDatedebut($datenow->format('Y-m-d'));
                $form->get('heuredebut')->setData($timenow); //$this->initHeuredebut($timenow->format('H:i:s'));
                //$form->get('dateEndPeriod')->setData( $timenow); //$this->initDatefin($datenow->format('Y-m-d'));
                $form->get('heurefin')->setData($timenow); //$this->initHeurefin($timenow->format('H:i:s'));

                }else{   // on passe une date en get

                //$form->get('dateStartPeriod')->setData($period->getStartPeriod()); //initdebutperiod($addate));
                $form->get('heuredebut')->setData($appointment->getStartime()); //$this->initdebutalong($along->format('H:i:s')));
                //$form->get('dateEndPeriod')->setData($period->getEndPeriod()); //$this->initfinperiod($addate));
                $form->get('heurefin')->setData($this->initfinalong($appointment)); //$this->initfinalong($along)->format('H:i:s')));

                }

                return $form;
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


        public function initfieldsdate($appointment, $form)
        {

                if(!isset($appointment)){
                    return;
                }

                $form->get('datedebut')->setData($appointment->getStart());
                $form->get('heuredebut')->setData($appointment->getStart());
                $form->get('datefin')->setData($appointment->getEnd());
                $form->get('heurefin')->setData($appointment->getEnd());

                return $form;
        }

        public function recDatedebut($editdate) 
        {
                return $editdate->format('Y-m-d');
                
        }
        public function recHeuredebut ($editdate)
        {
                return $editdate->format('H:i:s');
                
        }     
        public function rectDatefin($editdatefin) 
        {
                return $editdatefin->format('Y-m-d');
                
        }
        public function recHeurefin($editdatefin) 
        {
                return $editdatefin->format('H:i:s');
               
        }

        public function convertorDateStringStart($form)
        {       
                $okdate = DateTime::createFromFormat('Y-m-d H:i:s', ($form->get('datedebut')->getData()->format('Y-m-d')).' '.($form->get('heuredebut')->getData()->format('H:i:s')));  
                 return $okdate;        
        }

        public function convertorDateStringEnd($form)
        {                             
                return DateTime::createFromFormat('Y-m-d H:i:s', ($form->get('datefin')->getData()->format('Y-m-d')).' '.($form->get('heurefin')->getData()->format('H:i:s')));
        }

         public function convertorDateStringPeridoStart($form)
        {       
                $okdate = DateTime::createFromFormat('Y-m-d H:i:s', ($form->get('dateStartPeriod')->getData()->format('Y-m-d')).' '.($form->get('dateStartPeriod')->getData()->format('H:i:s')));  
                 return $okdate;        
        }

        public function convertorDateStringPeriodEnd($form)
        {                             
                return DateTime::createFromFormat('Y-m-d H:i:s', ($form->get('dateEndPeriod')->getData()->format('Y-m-d')).' '.($form->get('dateEndPeriod')->getData()->format('H:i:s')));
        }

       

        public function addAppointMarket($appointment, $form)
        { 
            $durestart=$form->get('heuredebut')->getData();  
            $dureend=$form->get('heurefin')->getData();
            $appointment->setStarttime($durestart);
            $appointment->setAlongtime($dureend->diff($durestart)->format('%H:%I:%S'));
            $appointment->setStatut(true);
            $appointment->setConfirmed(false);
            $appointment->setName($form->get('name')->getData());
            
            return $appointment;       
        }

        public function addAppointRdv($appointment, $form)
        { 
            $durestart=$form->get('heuredebut')->getData();  
            $dureend=$form->get('heurefin')->getData();
            $appointment->setStarttime($durestart);
            $appointment->setAlongtime($dureend->diff($durestart)->format('%H:%I:%S'));
            $appointment->setStatut(true);
            $appointment->setConfirmed(false);
            $textname="rendez-vous avec ".$form->get('name')->getData()."";
            
            return $appointment;       
        }


        public function addCallBack($appointment, $callbackappoint)  
        { 
            $instCallbacksAppoint=new CallbacksAppoint();
            $instCallbacksAppoint->setchoiceCallback($callbackappoint);
            $appointment->addFrequenceCallback($instCallbacksAppoint);  

            return $instCallbacksAppoint  ;

        }

        public function addPeriodMarket($simple, $appointment, $form, $period=null)
        {
            // intialisation des dates de periodes

            // ra jout d'un test pour la version simplifié sans periode par défaut :
            if($simple){
                $datedepart = new DateTime();
                $datestop= new DateTime();
                $datestop->modify("+2 year");

            }else{

                if($form->get("alongchoice")->getData()===0){
                    $datedepart = new DateTime();
                    $datestop= new DateTime();
                    $datestop->modify("+2 year");
                        
                }else{
                    $datedepart = $this->convertorDateStringPeridoStart($form);
                    $datestop= ($this->convertorDateStringPeriodEnd($form))->modify("+1 day");               
                }
            }

            $periodicity=$form->get("periodicity")->getData();
            $numrepete=$form->get("numberrepete")->getData();

            if($periodicity===5){                               // personnalisé
                $typerepete=$form->get("typerepete")->getData();
                $numrepete=$numrepete>1 ? $numrepete : 1;  
                $daysweek=implode("-",$form->get("daysweek")->getData());
                $daymobth=$form->get("daymonth")->getData();
            }else{                                              // automatique (choix: jour, semaine ...)
                $typerepete=$periodicity;
                $numrepete=1;
                // ici en version simplifié on impute le choix du jour
                $daysweek=implode("-",$form->get("daysweek")->getData());
                $daymobth=$form->get("daymonth")->getData();

                // sinon ce serait cette version :
                /*
                $daysweek=$datedepart->format('l');
                $daymobth=$datedepart->format('l');  
                */
            }
           
            if(!isset($period)){
            $Instperiods=new Periods();
            }
            else{
            $Instperiods=$period;
            }
            $Instperiods->setPeriodeChoice($periodicity);
            $Instperiods->setNumberrept($numrepete);
            $Instperiods->setTyperept($typerepete);          
            $Instperiods->setDaysweek($daysweek);
            $Instperiods->setDaymonth($daymobth);
            $Instperiods->setStartPeriod($datedepart);
            $Instperiods->setEndPeriod($datestop);
            $appointment->addIdPeriod($Instperiods);

        return $Instperiods;
    
    }

     public function addPeriodRdv($appointment, $form, $period=null)
        {  
            $datedepart = $this->convertorDateStringStart($form);
            $datestop= $this->convertorDateStringEnd($form);               
            $periodicity=0;
            $numrepete=1;
            $typerepete=1;
            $daysweek=$datedepart->format('l');
            $daymobth=$datedepart->format('l');  
  
            if(!isset($period)){
            $Instperiods=new Periods();
            }
            else{
            $Instperiods=$period;
            }
            $Instperiods->setPeriodeChoice($periodicity);
            $Instperiods->setNumberrept($numrepete);
            $Instperiods->setTyperept($typerepete);          
            $Instperiods->setDaysweek($daysweek);
            $Instperiods->setDaymonth($daymobth);
            $Instperiods->setStartPeriod($datedepart);
            $Instperiods->setEndPeriod($datestop);
            $appointment->addIdPeriod($Instperiods);

        return $Instperiods;
        
        }
        
}