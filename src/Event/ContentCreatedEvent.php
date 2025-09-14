<?php

namespace App\Event;


use App\Entity\Boards\Board;

class ContentCreatedEvent
{
    private Board $content;


    public function __construct(Board $content)
    {
        $this->content = $content;

    }

    public function getContent(): Board
    {
        return $this->content;
    }
}
