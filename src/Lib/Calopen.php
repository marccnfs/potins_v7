<?php


namespace App\Lib;

use DateTime;


class Calopen
{
    const tabdays=['Mon'=>'Lun', 'Tue'=>'Mar', 'Wed'=>'Mer', 'Thu'=>'Jeu', 'Fri'=>'Ven', 'Sat'=>'Sam', 'Sun'=>'Dim'];
    const tabdayslog=['Lun'=>'Lundi', 'Mar'=>'Mardi', 'Mer'=>'Mercredi', 'Jeu'=>'Jeudi', 'Ven'=>'Vendredi', 'Sam'=>'Samedi', 'Dim'=>'Dimanche'];
    const tabMonth=['01'=>'Janvier', '02'=>'Fevrier', '03'=>'Mars', '04'=>'Avril', '05'=>'Mai', '06'=>'Juin',
        '07'=>'Juillet','08'=>'Aout', '09'=>'Septembre', '10'=>'Octobre', '11'=>'Novembre', '12'=>'Decembre'];

    public static function Cal($tab): array
    {

        foreach ($tab->getTabuniquejso() as $key => &$theday){
            $theday['day']=self::tabdayslog[$key];
        }

        $date=new dateTime();
        $date->setTime(0,0,0);
        $timeday=$date->getTimestamp();
        //on recupere le jour, le mois et l'heure'
        $day=self::tabdays[$date->format('D')];
        $month=self::tabMonth[$date->format('m')];
        if($tab->getCongesjso()){
            if(array_key_exists($month,$tab->getCongesjso())){
                $openday['state']=false;
                return  $openday;
            }
        }
        if($tab->getTabuniquejso()) {
            if (!array_key_exists($day, $tab->getTabuniquejso())) {
                $openday['state'] = false;
                return $openday;
            }

            $timesforday=$tab->getTabuniquejso()[$day]['tab'];
            $timenow=time();
            foreach ($timesforday as $onetime){
                $start=($onetime['startminutes']*60)+$timeday;
                $end=$start+($onetime['alongminute']*60);
                if($start<=$timenow && $end >=$timenow) {
                    $openday['state']=true;
                    return  $openday;
                }
            }

            $openday['state']=false;
            return  $openday;
        }
        $openday['state']=false;
        return  $openday;
    }
}