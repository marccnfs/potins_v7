<?php


namespace App\DoctrineListener;

use App\Entity\Sector\Gps;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

class GpsListener
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function prePersist(Gps $gps, PrePersistEventArgs $event)
    {
        $gps->gpsSlug($this->slugger);
    }

    public function preUpdate(Gps $gps, PreUpdateEventArgs $event)
    {
        $gps->gpsSlug($this->slugger);
    }
}