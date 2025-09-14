<?php


namespace App\Event;

use App\Entity\UserMap\Heuristiques;
use App\Entity\Users\ProfilUser;
use App\Entity\Boards\Board;
use App\Entity\Users\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class DisptachEvent extends Event
{

    private ?Request $request;
    private Response $response;
    protected User $user;
    protected Board $website;
    private Heuristiques $heuristique;
    private ProfilUser $profil;


    /**
     *
     * @param User $user
     * @param ProfilUser $profil
     * @param Board $website
     * @param Request|null $request
     */
    public function __construct(User $user,ProfilUser $profil, Board $website, Request $request=null)
    {
        $this->request = $request;
        $this->user = $user;
        $this->website = $website;
        $this->profil=$profil;
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


    public function getHeuristique(): Heuristiques
    {
        return $this->heuristique;
    }

    public function setHeuristique($heuristique)
    {
        $this->heuristique = $heuristique;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
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