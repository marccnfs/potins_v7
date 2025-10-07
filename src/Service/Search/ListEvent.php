<?php


namespace App\Service\Search;

use App\Entity\Admin\OrderProducts;
use App\Repository\OrdersRepository;
use App\Repository\OrderProductsRepository;
use App\Repository\PostEventRepository;
use App\Service\Modules\Resator;
use Doctrine\ORM\NonUniqueResultException;


class ListEvent
{

    private OrdersRepository $orderrepo;
    private PostEventRepository $eventrepo;
    private Resator $resator;
    private OrderProductsRepository $orderProductsRepository;

    public function __construct(OrdersRepository $orderrepo, PostEventRepository $eventrepo, Resator $resator, OrderProductsRepository $orderProductsRepository)
    {  $this->orderrepo = $orderrepo;
        $this->eventrepo=$eventrepo;
        $this->resator=$resator;
        $this->orderProductsRepository = $orderProductsRepository;
    }

    public function listEventResa($locatemediaId): array
    {
        $events= $this->eventrepo->findEventByOneLocateMedia($locatemediaId);

        $orderProducts = $this->orderProductsRepository->findPendingByBoardIdWithAssociations($locatemediaId);


        $tabdatesevents=[];

        foreach ($events as $event) {
            $tabdatesevents[$event->getId()] = $this->initializeEventDates($event);
        }

        foreach ($orderProducts as $orderProduct) {
            $subscription = $orderProduct->getSubscription();
            if ($subscription === null) {
                continue;
            }

            $event = $subscription->getEvent();
            if ($event === null) {
                continue;
            }

            $eventId = $event->getId();
            if (!isset($tabdatesevents[$eventId])) {
                $tabdatesevents[$eventId] = $this->initializeEventDates($event);
            }

            $starttime = $subscription->getStarttime();
            if ($starttime === null) {
                continue;
            }

            $timestamp = $starttime->getTimestamp();
            if (!isset($tabdatesevents[$eventId]['date'][$timestamp])) {
                $tabdatesevents[$eventId]['date'][$timestamp] = $this->createEmptyScheduleBucket();
            }
            $this->appendOrderProductToSchedule($tabdatesevents[$eventId]['date'][$timestamp], $orderProduct);
        }
        return $tabdatesevents;
    }

    public function listallEvenstResa($locatemediaId): array
    {
        /*
        $orders = $this->orderrepo->findOrderEventByLocateMedia($locatemediaId);
        $tabdatesevents=[];
        foreach ($events as $l_event) {
            $tabdatesevents[$l_event->getId()]=$this->resator->BuildTabDateEvent($l_event);
            if ($orders) {
                foreach ($orders as $order){
                    if (!$order->isValider() && $order->getListproducts()[0]->getSubscription()->getEvent()->getId()===$l_event->getId()){
                        $producs=$order->getListproducts(); // toutes les inscriptions pour cet order
                        foreach ($producs as $prod){
                            foreach ($tabdatesevents[$l_event->getId()]['date'] as $key => $date) {
                                if ($key === $prod->getSubscription()->getStarttime()->getTimestamp()) {
                                    $tabdatesevents[$l_event->getId()]['date'][$key][] = $prod;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $tabdatesevents;
        */
        return $this->eventrepo->findEventByOneLocateMedia($locatemediaId);

        //return $this->eventrepo->findAllsEventsByOneLocateMedia($locatemediaId);
    }


    /**
     * @throws NonUniqueResultException
     */
    public function listResaOfOneEvent($id): array
    {
        $event= $this->eventrepo->findEventByOneId($id);
        $orders = $this->orderrepo->findOrderEventByEventId($id);
        $tabdatesevents=[];

            $tabdatesevents[$event->getId()]=$this->resator->BuildTabDateEvent($event);
            if ($orders) {
                foreach ($orders as $order){
                    if (!$order->isValider() && $order->getListproducts()[0]->getSubscription()->getEvent()->getId()===$event->getId()){
                        $producs=$order->getListproducts(); // toutes les inscriptions pour cet order
                        foreach ($producs as $prod){
                            foreach ($tabdatesevents[$event->getId()]['date'] as $key => $date) {
                                if ($key === $prod->getSubscription()->getStarttime()->getTimestamp()) {
                                    $tabdatesevents[$event->getId()]['date'][$key][] = $prod;
                                }
                            }
                        }
                    }
                }
            }

        return $tabdatesevents;
    }

    public function listPlayerPotin($id): array
    {
        //$events= $this->eventrepo->findEventByOnePotin($id);
        $orders = $this->orderrepo->findOrderEventByPotin($id);
        $tabplayer[]=18; //marc.de-jesus@conseiller-numerique.fr
        foreach ($orders as $l_order) {
            $tabplayer[]=$l_order->getNumclient()->getId();
        }
        return $tabplayer;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function listParticipantPotin($id): array
    {
        //$events= $this->eventrepo->findEventByOnePotin($id);
        $orders = $this->orderrepo->findOrderEventByEventId($id);

        $tabplayer=[];
        $cpt=1;
        foreach ($orders as $l_order) {
            $tabplayer[$cpt]['order']=$l_order->getNumclient()->getIdcustomer();
            $listparticpants=$l_order->getListproducts();

            foreach ($listparticpants as $l_part) {
                $tabplayer[$cpt]['part']=$l_part->getRegistered();
                $tabplayer[$cpt]['docs']=$l_part->getDocs();
            }
            $cpt++;
        }

        return $orders; //$listparticpants; //$tabplayer;
    }

    private function initializeEventDates($event): array
    {
        $eventData = $this->resator->BuildTabDateEvent($event);
        $formatted = [
            'event' => $eventData['event'] ?? $event,
            'date' => [],
        ];

        if (!empty($eventData['date'])) {
            foreach (array_keys($eventData['date']) as $timestamp) {
                $formatted['date'][$timestamp] = $this->createEmptyScheduleBucket();
            }
        }

        return $formatted;
    }

    private function createEmptyScheduleBucket(): array
    {
        return [
            'orders' => [],
            'participants' => [],
            'count' => 0,
        ];
    }

    private function appendOrderProductToSchedule(array &$schedule, OrderProducts $orderProduct): void
    {
        $registered = $orderProduct->getRegistered();
        if ($registered !== null) {
            $schedule['participants'][] = $orderProduct;
            $schedule['count']++;
        }

        $order = $orderProduct->getOrder();
        if ($order === null || $order->getId() === null) {
            return;
        }

        $orderId = $order->getId();
        if (!isset($schedule['orders'][$orderId])) {
            $schedule['orders'][$orderId] = [
                'order' => $order,
                'referent' => $this->buildReferentContext($orderProduct),
                'participants' => [],
            ];
        }

        if ($registered !== null) {
            $schedule['orders'][$orderId]['participants'][] = $orderProduct;
        }
    }

    private function buildReferentContext(OrderProducts $orderProduct): array
    {
        $order = $orderProduct->getOrder();
        $numClient = $order?->getNumclient();
        $customer = $numClient?->getIdcustomer();
        $profil = $customer?->getProfil();

        $firstname = $profil?->getFirstname();
        $lastname = $profil?->getLastname();
        $displayName = trim(($firstname ?? '') . ' ' . ($lastname ?? ''));

        $email = $customer?->getEmailcontact();
        if ($email === null && $profil !== null) {
            $email = $profil->getEmailsecours();
        }

        $phone = $profil?->getTelephonemobile();
        if ($phone === null && $profil !== null) {
            $phone = $profil->getTelephonefixe();
        }

        return [
            'name' => $displayName !== '' ? $displayName : null,
            'email' => $email,
            'phone' => $phone,
            'customer' => $customer,
            'profil' => $profil,
        ];
    }
}
