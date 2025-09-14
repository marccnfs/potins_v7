<?php

namespace App\EventSubscriber;

use App\Entity\Users\Contacts;
use App\Entity\Users\ProfilUser;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private Contacts $contact;

    public function __construct()
    {
        $this->contact = new Contacts();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['setContactProfil'],
        ];
    }

    public function setContactProfil(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof ProfilUser)) {
            return;
        }
        $this->contact->setUseridentity($entity);
        $this->contact->setEmailCanonical($entity->getEmailfirst());
        $entity->setContact($this->contact);
    }
}