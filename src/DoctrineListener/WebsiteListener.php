<?php


namespace App\DoctrineListener;

use App\Entity\Boards\Board;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class WebsiteListener
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function prePersist(Board $board, PrePersistEventArgs $event)
    {
        $board->boardSlug($this->slugger);
    }

    public function preUpdate(Board $board, PreUpdateEventArgs $event)
    {
        $board->boardSlug($this->slugger);
    }
}