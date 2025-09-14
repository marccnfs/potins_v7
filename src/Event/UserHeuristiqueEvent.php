<?php

namespace App\Event;


use App\Entity\Users\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserHeuristiqueEvent extends Event
{
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return UserHeuristiqueEvent
     */
    public function setUser(User $user): UserHeuristiqueEvent
    {
        $this->user = $user;
        return $this;
    }



}