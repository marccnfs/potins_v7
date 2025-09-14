<?php


namespace App\Event;


use App\Entity\Users\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterUserResponseEvent extends UserEvent
{
    private Response $response;

    /**
     * FilterUserResponseEvent constructor.
     *
     * @param User $user
     * @param Request       $request
     * @param Response      $response
     */
    public function __construct(User $user, Request $request, Response $response)
    {
        parent::__construct($user, $request);
        $this->response = $response;
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
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}