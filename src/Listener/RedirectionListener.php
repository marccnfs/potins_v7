<?php

namespace App\Listener;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class RedirectionListener
{

    private ?TokenInterface $securityTokenStorage;
    private UrlGeneratorInterface $router;
    private RequestStack $requestStack;

    public function __construct(TokenStorageInterface $container, RequestStack $requestStack, UrlGeneratorInterface $router)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->securityTokenStorage = $container->getToken();
    }

    public function onKernelRequest(RequestEvent $event)
    {

        $route = $event->getRequest()->attributes->get('_route');
        if ($route == 'page_livraison' || $route == 'page_validation') {
            if ($this->requestStack->getSession()->has('panier')) {
                if (count($this->requestStack->getSession()->get('panier')) == 0)
                    $event->setResponse(new RedirectResponse($this->router->generate('page_panier')));
            }

            if (!is_object($this->securityTokenStorage->getUser())) {
                $this->requestStack->getSession()->getFlashBag()->add('notification','Vous devez vous identifier');
                $event->setResponse(new RedirectResponse($this->router->generate('app_login')));
            }
        }
    }
}