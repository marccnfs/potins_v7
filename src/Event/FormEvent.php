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
    private ?Response $response = null;          // ✅ nullable + valeur par défaut
    private ?Heuristiques $heuristique = null;   // (si tu l’utilises, idem nullable)


    public function __construct(FormInterface $form, ?Request $request = null, ?Heuristiques $heuristique = null)
    {
        $this->form = $form;
        $this->request = $request;
        $this->heuristique = $heuristique;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setResponse(Response $response): void   // ✅ setter explicite
    {
        $this->response = $response;
    }

    public function getResponse(): ?Response                // ✅ retour nullable
    {
        return $this->response;
    }

    public function getHeuristique(): ?Heuristiques
    {
        return $this->heuristique;
    }

    public function setHeuristique(Heuristiques $heuristique): void
    {
        $this->heuristique = $heuristique;
    }
}
