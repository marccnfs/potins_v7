<?php

// src/EventSubscriber/RequireParticipantSubscriber.php
namespace App\EventSubscriber;

use App\Attribute\RequireParticipant;
use App\Security\ParticipantProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;

class RequireParticipantSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ParticipantProvider $provider,
        private UrlGeneratorInterface $urlGen
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [ KernelEvents::CONTROLLER => 'onController' ];
    }

    public function onController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->isProtected($request)) return;

        $participant = $this->provider->getCurrent();
        if (!$participant) {
            $url = $this->urlGen->generate('participant_entry'); // ta page d’entrée/connexion participant
            $event->setController(fn() => new RedirectResponse($url));
            return;
        }

        // Expose le participant pour le resolver & les templates
        $request->attributes->set('_participant', $participant);
    }

    private function isProtected(Request $request): bool
    {
        $ctrl = $request->attributes->get('_controller');
        if (!\is_array($ctrl) && !\is_string($ctrl)) return false;

        // Récupère la Reflection (Symfony normalise en [instance, 'method'] le plus souvent)
        try {
            if (\is_array($ctrl)) {
                $ref = new \ReflectionMethod($ctrl[0], $ctrl[1]);
                $classAttr = (new \ReflectionClass($ctrl[0]))->getAttributes(RequireParticipant::class);
                $methodAttr = $ref->getAttributes(RequireParticipant::class);
                return !empty($classAttr) || !empty($methodAttr);
            } elseif (\is_string($ctrl) && str_contains($ctrl, '::')) {
                [$class, $method] = explode('::', $ctrl, 2);
                $ref = new \ReflectionMethod($class, $method);
                $classAttr = (new \ReflectionClass($class))->getAttributes(RequireParticipant::class);
                $methodAttr = $ref->getAttributes(RequireParticipant::class);
                return !empty($classAttr) || !empty($methodAttr);
            }
        } catch (\Throwable) {}

        return false;
    }
}

