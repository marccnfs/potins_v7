<?php


namespace App\Event;


use App\Entity\Users\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterUserResponseEvent extends UserEvent
{
    private ?Response $response=null;


    public function __construct(User $user, Request $request)
    {
        parent::__construct($user, $request);
        $this->user = $user;
        $this->request = $request;
    }


    public function getResponse():?Response
    {
        return $this->response;
    }

    /**
     * Sets a new response object.
     *
     * @param Response $response
     */
    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }
}
