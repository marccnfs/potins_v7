<?php

namespace App\Calendar;


use \DateInterval;

class Monthcalendar {

	private $days=['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche']; 
	private $daysdate=['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']; 
	private $daysmob=['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']; 
	private $months=['Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre' ];
	private $month;
	private $year;
	private $week;
	private $monthletter;
	private $day;
	private $datenext=[];
	private $dateprevious=[];
	private $appointments=[];
	private $debutmois;
	private $finmois;


/**
* Month constructor
* @param int month le mois compris entre 1 et 12
* @param int year l'année en cours
*/

	public function __construct(?string $mois = null, ?string $annee = null)
	{

		if($mois === null || $annee === null) {
			$month = intval(date('m'));
			$year = intval(date('Y'));
		}else{

			$month=intval($mois);
			$year=intval($annee);
		
			if ($month < 1 || $month > 12) {
			 throw new \Exception("le mois $month n'est pas valide", 1);
			}
			if ($year < 1970)  {
				throw new \Exception("l'année $year est inferieure à 1970", 1);
			}
		}
		
		$this->month=$month;
		$this->year=$year;
		$this->month_letter=$this->months[$month-1];

		$debutmois = $this->getStartingDay(); 					// datetime du premier jour du mois
		$finmois = $this->getFinishDay($debutmois)->add(new DateInterval('PT23H59M59S'));

		
		$this->debutmois=$debutmois;
		$this->finmois=$finmois;

		$start = $debutmois->modify('last monday');  	// premier lundi avant le mois en cours
		$end = $debutmois->modify('+1 month -1 day');  	// dernier jour du mois

		

		$startweek = intval($start->format('W'));
		$endweek = intval($end->format('W'));

		if($endweek === 1) {
			$endweek = intval($end->modify('-7 days')->format('W')) + 1;
		}

		$weeks = ($endweek - $startweek) +1; // calcul du nombre de semaine a afficher
		


		$this->week=$weeks;
		$this->day=$start;					//  on attribut à day le premier lundi avant le mois

			if($weeks < 0)					//  si lundi est le 1er du mois ou semaine a cheval sur année précedent = nb week négatif
			{

				$this->week= intval($end->format('W'));		// dans ce cas on fixe le nombre de semaine a chiifre de la semaine du dernier jour
				$this->day=$debutmois;							// on fixe le premier jour non plus en fonction du dernier lundi mais du premier jour/mois

			}
	


		$this->datenext=$this->nextDate();
		$this->dateprevious=$this->previousDate();
	

		
	}

	public function getDebutmois()
	{

			 return $this->debutmois;

	}

	public function getFinmois()
	{
			
			 return $this->finmois;

	}

	public function getMonthletter()
	{
			
			 return $this->monthletter;

	}

	public function getDay()
	{
			
			 return $this->day;

	}

	public function getDays(): array
	{
			
			 return $this->days;

	}

	public function getDaysdate(): array
	{
			
			 return $this->daysdate;

	}


	public function getDaysmob(): array
	{
			
			 return $this->daysmob;

	}

	public function getDateprevious(): array
	{
			
			 return $this->dateprevious;

	}

	public function getDatenext(): array
	{

			 return $this->datenext;

	}


	public function getYear()
	{

			return $this->year;

	}

	public function getWeek():int {


		return $this->week;

	}

	public function getMonth() {


		return $this->month;

	}

    public function getMonths() {


        return $this->months;

    }
	

	/**
	* renvoi le premier jour du mois
	* @return \DateTimeInterface
	*/

	public function getStartingDay(): \DateTimeInterface
	{
				
		return new \DateTimeimmutable("{$this->year}-{$this->month}-01");
				
	}


	/**
	* renvoi le dernier jour du mois
	* @return \DateTimeInterface
	*/

	public function getFinishDay($datemois): \DateTimeInterface
	{
				
		return $datemois->modify('last day of this month');
				
	}



	/**
	* est ce que le jour est dans le mois affiché
	* @param \DateTimeInterface $date
	* @return bool
	*/

	public function withinMonth(\DateTimeInterface $date): bool
	{
				
		return $this->getStartingDay()->format('Y-m') === $date->format('Y-m');
				
	}
	

    public function previousDate(): array
    {

    	$moisprevious=$this->month-1;
        $yearprevious=$this->year;
     
        if($moisprevious < 1 )
         {
            $moisprevious =12;
            $yearprevious=$this->year-1;
         }

        $dateprevious= array("month"=> $moisprevious, "year" => $yearprevious );

        return $dateprevious;

    }
    public function nextDate(): array

    {   
        $moisnext=$this->month+1;
        $yearnext=$this->year;
        
        if($moisnext > 12)
         {
            $moisnext =1;
            $yearnext=$this->year+1;
         }

         $datenext= array("month"=> $moisnext, "year" => $yearnext );

        return $datenext;
            
    }
}