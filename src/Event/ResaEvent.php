<?php


namespace App\Event;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ResaEvent extends Event
{
    protected $resa;
    protected $user;
    protected $provider;

    public function __construct($resa, $provider, UserInterface $user)
    {
        $this->user=$user;
        $this->resa=$resa;
        $this->provider = $provider;
    }

    /**
     * @return mixed
     */
    public function getResa()
    {
        return $this->resa;
    }

    /**
     * @return UserInterface
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    /**
     * @param mixed $resa
     */
    public function setResa($resa): void
    {
        $this->resa = $resa;
    }

    /**
     * @return mixed
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param mixed $provider
     */
    public function setProvider($provider): void
    {
        $this->provider = $provider;
    }

}
