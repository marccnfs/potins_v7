<?php

namespace App\Calendar;



class Periodcalendar {

	private $days=['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche']; 
	private $daysmob=['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']; 
	private $week;
	private $day;
	private $appointments=[];


/**
* Week constructor
* @param int week le jour compris entre 1 et 52
*/

	public function __construct(?int $week = null)
	{
				
		if($week === null) {
			$week = intval(date('W'));
		}

		if ($month < 1 || $month > 52) {
		 throw new \Exception("la semaine $week n'est pas valide", 1);
		}
		
		
		$this->week=$week;
		$this->week_letter=$this->days[0];

		$debuweek = "l";			
		$finweek = "d";	

		$startweek = intval($start->format('W'));
		$endweek = intval($end->format('W'));
		
			
	}

	public function getDebutmois()
	{

			 return $this->debutmois;

	}

	public function getFinmois()
	{
			
			 return $this->finmois;

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