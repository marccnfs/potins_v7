<?php


namespace App\DoctrineListener;

use App\Entity\Marketplace\Offres;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

class OffreListener
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function prePersist(Offres $offre, PrePersistEventArgs $event)
    {
        $offre->offreSlug($this->slugger);
    }

    public function preUpdate(Offres $offre, PreUpdateEventArgs $event)
    {
        $offre->offreSlug($this->slugger);
    }
}