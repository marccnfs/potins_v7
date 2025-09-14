<?php

namespace App\Calendar;


use \DateInterval;


class WeekCalendar {

	private $days=['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche']; 
	private $daysdate=['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']; 
	private $daysmob=['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']; 
	private $months=['Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre' ];
	private $dateweek;

	private $week;
	private $year;

	private $month_letter;
	private $day;

	private $datenext=[];
	private $dateprevious=[];
	private $appointments=[];

	private $debutweek;
	private $finweek;


/**
* Month constructor
* @param int month le mois compris entre 1 et 12
* @param int year l'année en cours
*/

	public function __construct(?string $numweek = null)
	{
		if($numweek === null) {
			$week = intval(date('W'));
			$year = intval(date('Y'));		
		}else{
			$week=intval($numweek);
			$year = intval(date('Y'));
				if ($week < 1 || $week > 52) {
				 throw new \Exception("la semaine $numweek n'est pas valide", 1);
				}
		}
		$this->week=$week;
		$this->year=$year;
		//$this->month_letter=$this->months[$month-1];
		$debutweek=$this->getStartingDay();
		date_time_set($debutweek, 0, 0);
		$calfinweek= new \DateTimeimmutable($debutweek->format('Y-m-d'));
		$finweek = $this->getFinishDay($calfinweek)->add(new DateInterval('PT23H59M59S'));
		$this->debutweek=$debutweek;
		$this->finweek=$finweek;
		$this->day=$debutweek;

		//$start = $debutweek->modify('last monday');  	// premier lundi avant le mois en cours
		//$end = $debutmois->modify('+1 week -1 day');  	// dernier jour du mois

		//$startweek = intval($start->format('W'));
		//$endweek = intval($end->format('W'));

		//if($endweek === 1) {
		//	$endweek = intval($end->modify('-7 days')->format('W')) + 1;
		//}

		//$weeks = ($endweek - $startweek) +1; // calcul du nombre de semaine a afficher
		
		$this->datenext=$this->nextDate();
		$this->dateprevious=$this->previousDate();

	}

	public function getDebutweek()
	{
			 return $this->debutweek;
	}

	public function getFinweek()
	{
			 return $this->finweek;
	}

	public function getMonth_letter()
	{
			 return $this->month_letter;
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

	/**
	* renvoi le premier jour du mois
	* @return \DateTimeInterface
	*/

	public function getStartingDay(): \DateTimeInterface
	{		
		$firstday= new \DateTime();
		$firstday->setISODate($this->year, $this->week);
		return $firstday;			
	}

	/**
	* renvoi le sunday de la semaine en question
	* @return \DateTimeInterface
	*/

	public function getFinishDay($firstday): \DateTimeInterface
	{			
		return $firstday->modify('next sunday');		
	}


	/**
	* est ce que le jour est dans la semaine affichée
	* @param \DateTimeInterface $date
	* @return bool
	*/
	public function withinMonth(\DateTimeInterface $date): bool
	{
		return $this->getStartingDay()->format('Y-m') === $date->format('Y-m');
	}

    public function previousDate(): array
    {
    	$weekprevious=$this->week-1;
        $yearprevious=$this->year;
        if($weekprevious < 1 )
         {
            $weekprevious =52;
            $yearprevious=$this->year-1;
         }
        $dateprevious= array("week"=> $weekprevious, "year" => $yearprevious );
        return $dateprevious;
    }

    public function nextDate(): array
    {   
        $weeknext=$this->week+1;
        $yearnext=$this->year;
        if($weeknext > 52)
         {
            $weeknext =1;
            $yearnext=$this->year+1;
         }
         $datenext= array("week"=> $weeknext, "year" => $yearnext );
        return $datenext;
    }

   /* function getStartingDay($week,$year,$format="d/m/Y") {

		//$firstDayInYear=date("N",mktime(0,0,0,1,1,$year));
		
		//dump($testday);
		
		if ($firstDayInYear<5){
		$shift=-($firstDayInYear-1)*86400;
		dump($firstDayInYear);
		
		}else{
		$shift=(8-$firstDayInYear)*86400;
		dump($firstDayInYear);
		}
		if ($week>1) $weekInSeconds=($week-1)*604800; else $weekInSeconds=0;
		$timestamp=mktime(0,0,0,1,1,$year)+$weekInSeconds+$shift;
		$timestamp_vendredi=mktime(0,0,0,1,5,$year)+$weekInSeconds+$shift;
		dump($timestamp);
		dump($timestamp_vendredi);
		die();
		

		return new \DateTimeimmutable("Monday this week"); //,"dimanche " . date($format,$timestamp_vendredi));

		}

		//$debut_fin_semaine = get_lundi_vendredi_from_week(5, 2012);
		//echo $debut_fin_semaine[0] . " - " . $debut_fin_semaine [1];
		//affichera Lundi 30/01/2012 - Vendredi 03/02/2012
		*/
}