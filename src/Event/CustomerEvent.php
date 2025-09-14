<?php


namespace App\Event;

use App\Entity\Customer\Customers;
use App\Entity\UserMap\Heuristiques;
use App\Entity\Users\ProfilUser;
use App\Entity\Boards\Board;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerEvent extends Event
{
    private ?Request $request;
    private Response $response;
    protected Customers $customer;
    protected Board $website;
    private ProfilUser $profil;

    /**
     *
     * @param Customers $customer
     * @param Board $website
     * @param Request|null $request
     */
    public function __construct(Customers $customer, Board $website, Request $request=null)
    {
        $this->request = $request;
        $this->customer = $customer;
        $this->website = $website;
        $this->profil=$customer->getProfil();
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

    public function getCustomer(): Customers
    {
        return $this->customer;
    }

    public function setCustomer(Customers $customer): void
    {
        $this->customer = $customer;
    }

    public function getWebsite(): Board
    {
        return $this->website;
    }

    public function setWebsite(Board $website): void
    {
        $this->website = $website;
    }

    public function getProfil(): ProfilUser
    {
        return $this->profil;
    }

    public function setProfil(ProfilUser $profil): void
    {
        $this->profil = $profil;
    }

}