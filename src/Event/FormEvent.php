<?php


namespace App\Event;

use App\Entity\UserMap\Heuristiques;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FormEvent extends Event
{

    private FormInterface $form;
    private ?Request $request;
    private ?Response $response;
    private ?Heuristiques $heuristique;

    /**
     * FormEvent constructor.
     *
     * @param FormInterface $form
     * @param Request       $request
     */
    public function __construct(FormInterface $form, Request $request)
    {
        $this->form = $form;
        $this->request = $request;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getHeuristique()
    {
        return $this->heuristique;
    }

    public function setHeuristique(Heuristiques $heuristique)
    {
        $this->heuristique = $heuristique;
    }
}