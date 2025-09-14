<?php


namespace App\Event;


use App\Entity\Users\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class UserEvent extends Event
{

    protected ?Request $request;
    protected User $user;
    private Response $response;

    /**
     * UserEvent constructor.
     *
     * @param User $user
     * @param Request|null  $request
     */
    public function __construct(User $user, Request $request = null)
    {
        $this->user = $user;
        $this->request = $request;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

     function getResponse()
    {
        return $this->response;
    }
}