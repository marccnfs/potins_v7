<?php


namespace App\Service\Modules;

use App\Entity\Agenda\Appointments;
use \DateTime;
use App\Entity\Agenda\Periods;
use App\Entity\Agenda\CallbacksAppoint;
use \DateInterval;

Class Marketcator
{
      

    public function newMarket($form, $addate)
    {                
                $datenow = $addate;
                $timedeb=DateTime::createFromFormat('H:i:s' , "08:00:00");
                $timeterm=DateTime::createFromFormat('H:i:s' , "13:00:00");             
                $form->get('dateStartPeriod')->setData($datenow); //$this->initDatedebut($datenow->format('Y-m-d'));
                $form->get('heuredebut')->setData($timedeb); //$this->initHeuredebut($timenow->format('H:i:s'));
                $form->get('dateEndPeriod')->setData($datenow); //$this->initDatefin($datenow->format('Y-m-d'));
                $form->get('heurefin')->setData($timeterm); //$this->initHeurefin($timenow->format('H:i:s'));
                $form->get('dateStartPeriodone')->setData($datenow); //$this->initDatedebut($datenow->format('Y-m-d'));
                $form->get('heuredebutone')->setData($timedeb); //$this->initHeuredebut($timenow->format('H:i:s'));
                $form->get('heurefinone')->setData($timeterm); //$this->initHeurefin($timenow->format('H:i:s'));
                $form->get("numberrepete")->setData(0);
                $form->get("typerepete")->setData(1);
                //$form->get("callback")->setData(0); // desactivé en version simple
                //$form->get("daymonth")->setData(1);
                $form->get("alongchoice")->setData(0); 
                $form->get("periodicity")->setData("1");    

                return $form;
    }

    public function addPeriodMarket($appointment, $form, $period=null)
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
            $Instperiods->setTyperept($typerepete);          
            $Instperiods->setDaysweek($daysweek);
            //$Instperiods->setDaymonth($daymobth);
            $Instperiods->setStartPeriod($durestart);
            $Instperiods->setAlongPeriod($dureend->diff($durestart)->format('%H:%I:%S'));
            $appointment->addIdPeriod($Instperiods);

        return $Instperiods;
    
    }

    /**
     * @param $appointment Appointments
     * @param $form
     * @return mixed
     */
    public function addAppointMarket($appointment, $form)
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

            
            return $appointment;       
        }



    public function editMarket($appointment, $periods, $form)
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
                $form->get("typerepete")->setData($period->getTyperept());
                $form->get("periodicity")->setData($period->getPeriodeChoice());  
                $form->get("typerepete")->setData($period->getTyperept());
                $form->get("daysweek")->setData(explode("-",$period->getDaysweek()));
                //$form->get("callback")->setData(0); // desactivé en version simple
                //$form->get("daymonth")->setData(1); 
  
                return $form;
    }


    public function  majPeriodMarket($periods, $form)
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
            $period->setTyperept($typerepete);          
            $period->setDaysweek($daysweek);
            //$Instperiods->setDaymonth($daymobth);
            $period->setStartPeriod($durestart);
            $period->setAlongPeriod($dureend->diff($durestart)->format('%H:%I:%S'));

        return $period;
    }


    public function  majAppointMarket($appointment, $form)
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


    public function convertorDateStringPeridoStart($form)
    {       
                $okdate = DateTime::createFromFormat('Y-m-d H:i:s', ($form->get('dateStartPeriod')->getData()->format('Y-m-d')).' '.($form->get('heuredebut')->getData()->format('H:i:s')));  
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

   

   
    public function addCallBack($appointment, $callbackappoint)  
    { 
            $instCallbacksAppoint=new CallbacksAppoint();
            $instCallbacksAppoint->setchoiceCallback($callbackappoint);
            $appointment->addFrequenceCallback($instCallbacksAppoint);  

            return $instCallbacksAppoint  ;

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


        
}