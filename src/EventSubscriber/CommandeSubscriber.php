<?php


namespace App\EventSubscriber;

use App\Entity\Admin\NumClients;
use App\Entity\Admin\Products;
use App\Event\OrderEvent;
use App\AffiEvents;
use App\Repository\Entity\ProductsRepository;
use App\Service\Gestion\AutoCommande;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class CommandeSubscriber implements EventSubscriberInterface
{
    protected EntityManagerInterface $userManager;

    private AutoCommande $autoCommande;

    public function __construct(EntityManagerInterface $em, AutoCommande $autoCommande)
    {
        $this->userManager = $em;
        $this->autoCommande = $autoCommande;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AffiEvents::ACTIVATION_SUCCESS => [
                ['newCommandeInscription', 10],
            ],
        ];
    }

    /**
     * @param OrderEvent $event
     * @throws NonUniqueResultException
     */
    public function newCommandeInscription(OrderEvent $event)
    {
        $client = $event->getNumClients();

        if ($client instanceof NumClients) {
            $this->autoCommande->newInscriptionCmd($client);
        }

    }
}