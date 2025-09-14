<?php


namespace App\Service\Calendar;


use DateInterval;
use DateTime;
use DateTimeImmutable;

class DefineDateCalendar
{

    public function Datortest(?string $datecal= null, ?string $indexmonth=null){

        $year=intval(date('Y'));
        $month=intval(date("m"));
        $week=intval(date('W'));
        $today=intval(date('d'));

        if($datecal !== null && $indexmonth === null){
            throw new \Exception("l'index du mois n'est pas renseigné", 1);
        }


        if($indexmonth !== null){
            $nbyear=floor($indexmonth/12);
            $restemonth=$indexmonth+($nbyear*12);
            if($indexmonth>0){
                $month=$month+$restemonth;
                $year=$year + $nbyear;
                if($indexmonth< 0){ // année en moins
                    $month=$month-$restemonth;
                    $year=$year - $nbyear;
                    }
            }

            // on test tout de meme pour eviter une erreur de calcul
            if ($month < 1 || $month > 12) {
                    throw new \Exception("le mois $month n'est pas valide", 1);
            }
            if ($year < 1970) {
                    throw new \Exception("l'année $year est inferieure à 1970", 1);
            }

        }else{
            if ($datecal !== null) {
                // todo faire un test pour savoir si la date est valide
                $theday=new DateTime("{$year}-{$month}-{$datecal}");
            }else{
                $theday= new DateTime();
            }

        }


        /* la partie week est géré côté client car on retourne un tableau des weeks
            par contre on peut numeroter les week pour ce tableau (numero de week par rapport a date()
        */

        $debutmois = new DateTimeimmutable("{$year}-{$month}-01");
        $weekfirst=intval($debutmois->format('W'));
        $finmois = $debutmois->modify('last day of this month')->add(new DateInterval('PT23H59M59S'));
        $weeklast=intval($finmois->format('W'));

        // le tableau des week pour le mois affichable
        $nbweek=$weeklast-$weekfirst;

        // nombre de semaine
        if($nbweek===0) throw new \Exception("il n'y a pas de semaine dans le calcul -erreur-", 1);

         $tabweek=[];
        for($i=0; $i <= $nbweek; $i++){
            $tabweek[$i]['week']=$nbweek;
            $firstday= new \DateTime();
            $firstday->setISODate($year, (strval($weekfirst+$i))); //retourne la date du permier jour de cette semaine dans cette année
            date_time_set($firstday, 0, 0); // initialise à 0h 0 minutes et 1 seconde
            $tabweek[$i]['startweek']=$firstday;
            $finweek= new \DateTimeimmutable($firstday->format('Y-m-d'));
            $tabweek[$i]['endweek']= $finweek->modify('next sunday')->add(new DateInterval('PT23H59M59S'));
        }

        $start = $debutmois->modify('last monday');  	// premier lundi avant le mois en cours
        $end = $debutmois->modify('+1 month -1 day');  	// dernier jour du mois

        // TODO : normalement $end et $finmois devrait etre identique ? à vérifer

        /* ici on part d'une seconde logique pour les week pour les faire commencer par un lundi (donc a cheval sur un mois possible)*/


        $startweekcalendar = intval($start->format('W'));
        $endweekcalendar = intval($end->format('W'));

        if($endweekcalendar === 1) {
            $endweek = intval($end->modify('-7 days')->format('W')) + 1;
            }

        $weekscalendar = ($endweekcalendar - $startweekcalendar) +1; // calcul du nombre de semaine a afficher

        // TODO : possible que $weekscalendar ne soit pas egal a $nbweek car a cheval sur mois avant ete mois après ? à vérifer
        $week=$weekscalendar;

        $oneday=$start;    //  on attribut à oneday le premier lundi avant le mois

            if($weekscalendar < 0)					//  si lundi est le 1er du mois ou semaine a cheval sur année précedent = nb week négatif
            {
                $week= intval($end->format('W'));		// dans ce cas on fixe le nombre de semaine a chiifre de la semaine du dernier jour
                $oneday=$debutmois;							// on fixe le premier jour non plus en fonction du dernier lundi mais du premier jour/mois
            }
        return [$debutmois, $finmois, $start,$end,$weekscalendar,$oneday,$tabweek, $theday, $week];
    }

}