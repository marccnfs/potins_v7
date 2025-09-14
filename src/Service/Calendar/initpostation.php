<?php


namespace App\Service\Calendar;


use App\Calendar\Monthcalendar;
use App\Entity\Agenda\Periods;
use App\Repository\Entity\PostRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class initpostation
{

    /**
     * @var ModuleRepository
     */
    private $postationrepo;
    private $tabPostation;
    private $daystab = [];
    private $daystabvide = [];
    private $month;

    public function __construct(PostRepository $postationRepository)
    {
        $this->postationrepo = $postationRepository;
    }

    /**
     * @param $idPostation
     * @param null $month
     * @param null $year
     * @return array
     * @throws \Exception
     */
    public function initOnePostationByMonth($idPostation, $month = null, $year = null)
    {
        try {
            $this->month = new Monthcalendar($month, $year);
        } catch (\Exception $e) {
            $this->month = new Monthcalendar();
        }

        $this->tabPostation = $this->postationrepo->findOnePostationWhitPeriodsforMonth($idPostation, $this->month->getDebutmois(), $this->month->getFinmois());

        if (null === $this->tabPostation) {
            throw new NotFoundHttpException("aucun evenement enregistré");
        }

        $this->listPeriods();
        return ['date' => $this->month, 'events' => $this->daystab, 'vide' => $this->daystabvide, 'postation'=>$this->tabPostation];
    }

    protected function listPeriods()
    {
        $periods=$this->tabPostation['idmodule']['appointment']['idPeriods'];

        /** @var $period Periods*/
        foreach ($periods as $period) {  // iteration des objets Events inclus dans
            $dateappoint = $period['startPeriod']->format('Y-m-d');
            $this->inputapoint($period, $dateappoint);
        }
        return ;
    }

    protected function inputapoint($period, $dateappoint)
    {
        if (!isset($this->daystab[$dateappoint])) {
            $this->daystab[$dateappoint] = [$period];
        } else {
            $this->daystab[$dateappoint][] = $period; //TODO: creer un tableau pour identifier les evenements
        }
        return;
    }
}