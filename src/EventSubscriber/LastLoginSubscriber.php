<?php


namespace App\EventSubscriber;

use App\Entity\Users\User;
use App\Event\UserEvent;
use App\AffiEvents;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LastLoginSubscriber implements EventSubscriberInterface
{
    protected EntityManagerInterface $userManager;

   
    public function __construct(EntityManagerInterface $em)
    {
        $this->userManager = $em;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            Affievents::SECURITY_IMPLICIT_LOGIN => 'onImplicitLogin',
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        );
    }

    public function onImplicitLogin(UserEvent $event)
    {
        $user = $event->getUser();
        $user->setLastLogin(new \DateTime());
        $this->userManager->persist($user);
        $this->userManager->flush();
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof User) {
            $user->setLastLogin(new DateTime());
            $this->userManager->persist($user);
            $this->userManager->flush();
        }
    }


}