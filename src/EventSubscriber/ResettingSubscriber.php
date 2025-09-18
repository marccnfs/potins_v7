<?php

namespace App\EventSubscriber;

use App\AffiEvents;
use App\Event\FilterUserResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResettingSubscriber implements EventSubscriberInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AffiEvents::RESETTING_RESET_COMPLETED => 'onResetCompleted',
        ];
    }

    public function onResetCompleted(FilterUserResponseEvent $event): void
    {
        if ($event->getResponse() !== null) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_login')));
    }
}
