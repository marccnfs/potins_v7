<?php

namespace App\Event;

use App\Entity\Posts\Post;
use Symfony\Contracts\EventDispatcher\Event;

class PotinCreatedEvent extends Event
{
    public const CREATE = 'potin.create';
    public const MAJ = 'potin.maj';
    public const SHOW_WEBSITE = 'potin.show';

    protected Post $potin;

    public function __construct(Post $potin)
    {
        $this->potin = $potin;
    }

    public function getPotin(): Post
    {
        return $this->potin;
    }
}
