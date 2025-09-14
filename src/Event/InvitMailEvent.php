<?php


namespace App\Event;

use App\Entity\Boards\Board;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InvitMailEvent extends Event
{
    private ?Request $request;
    private Response $response;
    protected string $mail;
    protected Board $website;

    /**
     * @param $mail
     * @param Board $website
     * @param Request|null $request
     */
    public function __construct($mail, Board $website, Request $request=null)
    {
        $this->request = $request;
        $this->mail = $mail;
        $this->website = $website;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function getMail(): string
    {
        return $this->mail;
    }

    public function setMail($mail): void
    {
        $this->mail = $mail;
    }

    public function getWebsite(): Board
    {
        return $this->website;
    }

    public function setWebsite(Board $website): void
    {
        $this->website = $website;
    }
}