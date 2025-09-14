<?php


namespace App\Calendar;

use App\Lib\Days;


class Calendar
{


    private $finweek;
    private $debutmois;
    private $findmois;
    private $start;
    private $end;
    private $oneday;
    private $tabweek;
    private $day;
    private $moisprevious=0;
    private $datenext=0;
    private $weekscalendar;
    private $theday;
    private $datecal;
    private $indexmonth;
    private $week;
    private $days;
    private $month;



    public function __construct($result)
    {
        $this->initcalendar($result);
    }

    public function initcalendar($datecalendar){

        // return [$debutmois, $finmois, $start,$end,$weekscalendar,$oneday,$tabweek, $theday, $week];

        $this->debutmois=$datecalendar[0];
        $this->findmois=$datecalendar[1];
        $this->start=$datecalendar[2];
        $this->end=$datecalendar[3];
        $this->weekscalendar=$datecalendar[4];
        $this->oneday=$datecalendar[5];
        $this->tabweek=$datecalendar[6];
        $this->theday=$datecalendar[7];
        $this->week=$datecalendar[8];
        $this->month=Days::MONTHS[($this->theday->format('m'))-1];
        $this->days=Days::DAYS;
    }


    /**
     * est ce que le jour est dans le mois affiché
     * @param \DateTimeInterface $date
     * @return bool
     */

    public function withinMonth(\DateTimeInterface $date): bool
    {
        return $this->debutmois->format('Y-m') === $date->format('Y-m');
    }


    public function previousDate(): string
    {

        $this->moisprevious=-1;
        $dateprevious = $this->moisprevious;

        return $this->moisprevious;

    }
    public function nextDate(): string

    {
        $this->moisprevious=+1;
        $dateprevious = $this->moisprevious;

        return $this->moisprevious;

    }

    /**
     * @return mixed
     */
    public function getFindmois()
    {
        return $this->findmois;
    }

    /**
     * @return mixed
     */
    public function getDebutmois()
    {
        return $this->debutmois;
    }

    public function getWeekscalendar()
    {

        return $this->weekscalendar;

    }

    public function getTabweek()
    {

        return $this->tabweek;

    }

    /**
     * @return array
     */
    public function getDatenext(): array
    {
        return $this->datenext;
    }

    /**
     * @return mixed
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @return mixed
     */
    public function getMonthletter()
    {
        return $this->monthletter;
    }

    /**
     * @return mixed
     */
    public function getWeek()
    {
        return $this->week;
    }

    /**
     * @return mixed
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @return mixed
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @return mixed
     */
    public function getOneday()
    {
        return $this->oneday;
    }

}