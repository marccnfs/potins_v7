<?php

namespace App\Event;

use App\Entity\Boards\Board;
use Symfony\Contracts\EventDispatcher\Event;

class WebsiteCreatedEvent extends Event
{
    public const CREATE = 'website.create';
    public const MAJ = 'website.maj';
    public const SHOW_WEBSITE = 'website.show';

    protected Board $website;

    public function __construct(Board $website)
    {
        $this->website = $website;
    }

    public function getWebsite(): Board
    {
        return $this->website;
    }
}
