<?php


namespace App\EventSubscriber;


use App\AffiEvents;
use App\Event\FilterUserResponseEvent;
use App\Event\UserEvent;
use App\Security\LoginManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AuthentificationSubscriber implements EventSubscriberInterface
{

    private LoginManagerInterface $loginManager;
    private string $firewallName;

    /**
     * AuthenticationListener constructor.
     *
     * @param LoginManagerInterface $loginManager
     * @param string $firewallName
     */
    public function __construct(LoginManagerInterface $loginManager, string $firewallName)
    {
        $this->loginManager = $loginManager;
        $this->firewallName = $firewallName;
    }


    public static function getSubscribedEvents(): array
    {
        return array(
            Affievents::REGISTRATION_COMPLETED => 'authenticate',
            Affievents::REGISTRATION_CONFIRMED => 'authenticate',
            Affievents::RESETTING_RESET_COMPLETED => 'authenticate',
        );
    }

    /**
     * @param FilterUserResponseEvent $event
     * @param $eventName
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function authenticate(FilterUserResponseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher): void
    {
        try {
            $this->loginManager->logInUser($this->firewallName, $event->getUser(), $event->getResponse());
            $event=new UserEvent($event->getUser(), $event->getRequest());
            $eventDispatcher->dispatch($event, Affievents::SECURITY_IMPLICIT_LOGIN );
        } catch (AccountStatusException $ex) {
            // We simply do not authenticate users which do not pass the user
            // checker (not enabled, expired, etc.).
        }
    }
}