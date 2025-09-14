<?php


namespace App\Service\Search;

use App\Repository\OrdersRepository;
use App\Repository\PostEventRepository;
use App\Service\Modules\Resator;
use Doctrine\ORM\NonUniqueResultException;


class ListEvent
{

    private OrdersRepository $orderrepo;
    private PostEventRepository $eventrepo;
    private Resator $resator;

    public function __construct(OrdersRepository $orderrepo, PostEventRepository $eventrepo, Resator $resator)
    {
        $this->orderrepo = $orderrepo;
        $this->eventrepo=$eventrepo;
        $this->resator=$resator;
    }

    public function listEventResa($locatemediaId): array
    {
        $events= $this->eventrepo->findEventByOneLocateMedia($locatemediaId);

        $orders = $this->orderrepo->findOrderEventByLocateMedia($locatemediaId);

        $tabdatesevents=[];
        foreach ($events as $l_event) {
            $tabdatesevents[$l_event->getId()]=$this->resator->BuildTabDateEvent($l_event);

            if ($orders) {
                foreach ($orders as $order){

                    if (!$order->isValider() && $order->getListproducts()[0]->getSubscription()->getEvent()->getId()===$l_event->getId()){

                        //  $tabdatesevents[$l_event->getId()]['order'][$order->getId]=$order;

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
}