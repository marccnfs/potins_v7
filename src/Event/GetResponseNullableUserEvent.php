<?php

namespace App\Event;

use App\Entity\Users\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Response user event that allows null user.
 */
class GetResponseNullableUserEvent extends Event
{
    /**
     * @var Response
     */
    private $response;
    /**
     * @var null|Request
     */
    protected $request;

    /**
     * @var User
     */
    protected $user;

    /**
     * UserEvent constructor.
     *
     * @param User $user
     * @param Request|null  $request
     */
    public function __construct(User $user=null, Request $request = null)
    {
        $this->user = $user;
        $this->request = $request;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

}