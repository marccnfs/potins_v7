<?php


namespace App\Calendar;


use App\Service\Calendar\DefineDateCalendar;

class BaseCalendar
{

    private $define;



    protected function initcalendar(DefineDateCalendar $define){
        $this->define=$define;
    }
}