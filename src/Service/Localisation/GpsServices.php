<?php


namespace App\Service\Localisation;



use App\Entity\Sector\Gps;
use App\Repository\GpsRepository;
use Doctrine\ORM\EntityManagerInterface;


class GpsServices
{

    private GpsRepository $gpsRepository;
    private EntityManagerInterface $em;


    public function __construct(GpsRepository $gpsRepository, EntityManagerInterface $em){
        $this->gpsRepository = $gpsRepository;
        $this->em = $em;
    }


    /**
     * @param $city
     * @return Gps|null
     */
    public function newGpsLocate($city): ?Gps
    {
            $gps = New Gps();
            if ($coordonnates = $city['centre']['coordinates']) {
                $gps->setLatloc($coordonnates[1]);
                $gps->setLonloc($coordonnates[0]);
            }
            $gps->setCity($city['nom']);
            $gps->setNameloc($city['nom']);
            $gps->setCode($city['codesPostaux'][0]);
            $gps->setInsee($city['code']);
            $gps->setPerimeter(0);
            $this->em->persist($gps);
            $this->em->flush();
            return $gps;
    }

}