<?php


namespace App\Service\Calendar;


use App\Calendar\Monthcalendar;
use App\Calendar\WeekCalendar;
use App\Entity\Agenda\Appointments;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;

class InitModule
{
    private $security;

    /**
     * @var ModuleRepository
     */
    private $moduleRepository;

    public function __construct(Security $security, ModuleRepository $moduleRepository)
    {
        $this->security = $security;
        $this->moduleRepository = $moduleRepository;
    }

    /**
     * @param $module
     * @param null $month
     * @param null $year
     * @return array
     * @throws \Exception
     */
    public function initModuleByMonth($module, $month = null, $year = null)
    {
        try {
            $month = new Monthcalendar($month, $year);
        } catch (\Exception $e) {
            $month = new Monthcalendar();
        }
        $start = $month->getDebutmois();
        $end = $month->getFinmois();

        $Events = $this->moduleRepository->findModuleByAppointWithPeriod($module['id'], $start, $end);
        if (null === $Events) {
            throw new NotFoundHttpException("aucun evenement enregistré");

        }
        return $this->listModuleByMonth($month, $start, $end, $Events);
    }

    /**
     * @param $month
     * @param $start
     * @param $end \DateTimeImmutable
     * @param $Events
     * @return array
     * @var $appoint Appointments
     */
    public function listModuleByMonth($month, $start, $end, $Events)
    {
        $daystab = [];
        $daystabvide = [];

        /** @var $appoint Appointments */
        /** @var $Event Module*/
        foreach ($Events as $Event) {  // iteration des objets Events inclus dans
            $appoint = $Event->getAppointment();
            $startappoint = $appoint->getStarttime();
            $endappoint = $appoint->getEndtime();
            $periods = $appoint->getIdPeriods();
            foreach ($periods as $period) {

                $typechoice = $period->getPeriodeChoice();

                if ($typechoice !== 1) {       // si il y a une periodicité
                    $numrepete = $period->getNumberrept();
                    $numrepete = $numrepete > 1 ? $numrepete : 1;
                    $typerepete = $period->getTyperept();

                    //$startappoint=$period->getStartPeriod();
                    //$along=$period->getAlongPeriod();
                    //$endappoint=$periodStart->add(new DateInterval('P0000-00-00T'.$along.''));

                    $daysweek = explode("-", $period->getDaysweek());

                    $interval = $startappoint->diff($endappoint)->format('%a'); // plage de validité de la periods en jour

                    switch ($typerepete) {       // periodicité par jour
                        case '1':
                            $firstday = $startappoint;
                            $endweekmonth = $end->modify('last day of this month');
                            $endsunday = $endweekmonth->modify('Sunday');
                            if ($interval > 1) {

                                while ($firstday <= $endsunday->modify('+1 day')) {
                                    if ($firstday >= $start && $firstday < $endappoint) {
                                        $dateappoint = $firstday->format('Y-m-d');
                                        $daystab = $this->inputapoint($daystab, $Event, $dateappoint);
                                        $firstday = $firstday->modify('+' . $numrepete . 'day');
                                    } else {
                                        $firstday = $firstday->modify('+' . $numrepete . 'day');
                                    }
                                }

                            } else {
                                if ($firstday >= $start && $firstday <= $end) {
                                    $dateappoint = $firstday->format('Y-m-d');
                                    $daystab = $this->inputapoint($daystab, $Event, $dateappoint);
                                }
                            }
                            break;

                        case '2':  // periodicité en semaine
                            $firstday = $startappoint;
                            $lastday = $endappoint;
                            //1er lundi du calendier (mois ou mois-1)
                            $jourmois = $start->modify('Monday this week');
                            while ($jourmois < $end) {  // on boucle sur le mois

                                if ($jourmois >= $firstday && $lastday >= $jourmois) { //si le debut appoint est egale/sup au jour du mois
                                    foreach ($daysweek as $day) {  //ici on cherche les jour renseigné
                                        $dayappoint = $jourmois->modify($day);
                                        $dateappoint = $dayappoint->format('Y-m-d');
                                        $daystab = $this->inputapoint($daystab, $Event, $dateappoint);
                                    }
                                }
                                $jourmois = $jourmois->modify('+' . $numrepete . 'week');
                            }
                            break;

                        case '3':  // periodicité en mois //todo
                            $daystab = $this->imputtabmonth($daystab, $interval, $numrepete, $start, $end, $startappoint, $Event);
                            break;
                    }

                } else {    // si pas de periodicité cas d'un evenement ponctuel ou post avec plusieurs dates
                    $dateappoint = $appoint->getStarttime()->format('Y-m-d');
                    $daystab = $this->inputapoint($daystab, $Event, $dateappoint);
                }
            }// fin du for periode
        } // fin du for sur Event

        return ['date' => $month, 'events' => $daystab, 'vide' => $daystabvide];
    }

    /**

     * @param null $week
     * @return array
     * @throws \Exception
     */
    public function initModuleByWeek($Event, $week = null)
    {
        $weekentity = new WeekCalendar($week);
        $start = $weekentity->getDebutweek();
        $end = $weekentity->getFinweek();
        $Events = $this->EventRepository->findEventByAppointWithPeriod($Event['id'], $start, $end);
        if (null === $Events) {
            throw new NotFoundHttpException("aucun evenement enregistré");
            $Events = [];
        }
        return $this->listEventByWeek($weekentity, $Events);
    }

    public function listModuleByWeek($week, $Events)
    {

        $daystab = [];
        $daystabvide = [];
        $tabEvents = [];
        $i = 0;

        $start = $week->getDebutweek();
        $end = $week->getFinweek();


        foreach ($Events as $Event) {  // iteration des objets Events inclus dans
            // le mois passé en argument de la methode
            $appoint = $Event->getAppointment();
            $startappoint = $appoint->getStarttime();
            $endappoint = $appoint->getEndtime();

            $periods = $appoint->getIdPeriods();

            foreach ($periods as $period) {

                $typechoice = $period->getPeriodeChoice();

                if ($typechoice !== 1) {
                    // si il y a une periodicité
                    $numrepete = $period->getNumberrept();
                    $numrepete = $numrepete > 1 ? $numrepete : 1;
                    $typerepete = $period->getTyperept();
                    $interval = $startappoint->diff($endappoint)->format('%a'); // plage de validité de la periods en jour

                    switch ($typerepete) {
                        case '1': //jour

                            $dayweek = $start;
                            $endtest = new \DateTimeimmutable($end->format('Y-m-d') . '+1 day'); // a revoir exactement

                            if ($interval > 1) {

                                while ($dayweek < $endtest) {
                                    if ($dayweek >= $start && $dayweek <= $end) {

                                        $day = $dayweek->format('l');
                                        $tabEvents[$i][$day] = $Event;
                                    }
                                    $dayweek = $dayweek->modify('+' . $numrepete . 'day');
                                }
                            } else {
                                if ($dayweek >= $start && $dayweek < $end) {
                                    $day = $dayweek->format('l');
                                    $tabEvents[$i][$day] = $Event;
                                }
                            }
                            break;

                        case '2':  // semaine
                            $daysweek = explode("-", $period->getDaysweek());
                            $dateday = $startappoint;
                            $lastday = $endappoint;

                            $dayweek = $start;

                            foreach ($daysweek as $day) {  //ici on cherche les jour renseigné
                                $dayappoint = $dayweek->modify($day);
                                if ($dayappoint >= $start && $dayappoint <= $end) {
                                    $tabEvents[$i][$day] = $Event;
                                }
                            }

                            break;

                        case '3':  // periodicité en mois a faire
                            $daystab = $this->imputtabmonth($daystab, $interval, $numrepete, $start, $end, $startappoint, $Event);
                            break;
                    }

                } else {    // si pas de periodicité cas d'un evenement ponctuel
                    $day = $startappoint->format('l');

                    $tabEvents[$i][$day] = $Event;;
                }
            }// fin du for periode

            $i++;
        } // fin du for sur Event


        return ['date' => $week, 'Events' => $tabEvents, 'vide' => $daystabvide];
    }





    public function inputapoint($daystab, $Event, $dateappoint)
    {
        if (!isset($daystab[$dateappoint])) {
            $daystab[$dateappoint] = [$Event];
        } else {
            $daystab[$dateappoint][] = $Event; //TODO: creer un tableau pour identifier les evenements
        }
        return $daystab;
    }

}

	