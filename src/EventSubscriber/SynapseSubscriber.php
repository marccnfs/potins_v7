<?php


namespace App\EventSubscriber;


use App\Entity\UserMap\Heuristiques;
use App\AffiEvents;
use App\Event\UserHeuristiqueEvent;
use App\Event\WebsiteCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SynapseSubscriber implements EventSubscriberInterface
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
        return [
            AffiEvents::COMMANDE_SUCCESS =>[
                ['newheuriheatinscription', 10],
            ],
            WebsiteCreatedEvent::SHOW_WEBSITE =>'logshowwebsite',
        ];
    }

    public function newheuriheatinscription(UserHeuristiqueEvent $event, $source="INSCRIPTION"){

        $user = $event->getUser();

            $heuristique = new Heuristiques($user);
            $sys=constant('Synapse::'.$source);
            $heuristique->setSem($sys[0]);
            $heuristique->setColor($sys[1]);
            $heuristique->setBinarycolor($sys[2]);
            $this->userManager->persist($heuristique);
            $this->userManager->flush();
        }

    public function logshowwebsite(WebsiteCreatedEvent $event, $source="INSCRIPTION"){

        $wb = $event->getWebsite();
        $hit=$wb->getHits();
        $hit->setLastdayshow(new \DateTime());
        $hit->setPubli($hit->getPubli()+1);
        $this->userManager->persist($wb);
        $this->userManager->flush();
    }
}