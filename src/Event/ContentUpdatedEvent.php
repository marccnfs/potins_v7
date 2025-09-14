<?php

namespace App\Event;


use App\Entity\Boards\Board;

class ContentUpdatedEvent
{
    private Board $content;
    private Board $previous;

    public function __construct(Board $content, Board $previous)
    {
        $this->content = $content;
        $this->previous = $previous;

    }

    public function getContent(): Board
    {
        return $this->content;
    }

    public function getPrevious(): Board
    {
        return $this->previous;
    }
}
