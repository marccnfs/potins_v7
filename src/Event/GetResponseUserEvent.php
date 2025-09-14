<?php


namespace App\Event;


use App\Entity\Users\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class GetResponseUserEvent extends Event
{

    private ?Response $response=null;
    protected ?Request $request;
    protected User $user;


    public function __construct(User $user, Request $request = null)
    {
        $this->user = $user;
        $this->request = $request;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }


    public function getUser(): User
    {
        return $this->user;
    }


    public function getRequest()
    {
        return $this->request;
    }


}