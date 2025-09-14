<?php


namespace App\Event;


use App\Entity\Admin\NumClients;
use App\Entity\Admin\Orders;
use Symfony\Contracts\EventDispatcher\Event;

class OrderEvent extends Event
{
    /**
     * @var NumClients
     */
    private $numClients;

    public function __construct(NumClients $numClients)
    {
        $this->numClients = $numClients;
    }

    /**
     * @return NumClients
     */
    public function getNumClients(): NumClients
    {
        return $this->numClients;
    }

    /**
     * @param NumClients $numClients
     * @return OrderEvent
     */
    public function setNumClients(NumClients $numClients): OrderEvent
    {
        $this->numClients = $numClients;
        return $this;
    }
}