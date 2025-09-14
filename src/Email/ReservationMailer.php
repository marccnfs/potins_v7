<?php


namespace App\Email;


use App\Entity\Customer\Customers;
use App\Entity\SpaceWeb\SpaceWebs;

class ReservationMailer
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendNewNotification(Customers $customer, SpaceWebs $providers)
    {
        $message = new \Swift_Message(
            'Nouvelle réservation',
            'Une nouvelle réservation : ici le détail.'
        );

        $message
            ->addTo($customer->getEmailcontact()) // Ici bien sûr il faudrait un attribut "email", j'utilise "author" à la place
            ->addFrom($providers->getEmailprovider())
        ;
        $this->mailer->send($message);
    }

}