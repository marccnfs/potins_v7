<?php


namespace App\DoctrineListener;

use App\Entity\Posts\Post;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostListener
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function prePersist(Post $post, PrePersistEventArgs $event)
    {
        $post->postSlug($this->slugger);
    }

    public function preUpdate(Post $post, PreUpdateEventArgs $event)
    {
        $post->postSlug($this->slugger);
    }
}