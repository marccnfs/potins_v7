<?php


namespace App\Service;

use App\Entity\Agenda\LogResa;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;


class LogAdmin
{

    private $security;
    private $em;


    public function __construct(Security $security, EntityManagerInterface $entitymanager)
    {
        $this->security=$security;
        $this->em=$entitymanager;
    }
    public function NewLogResa($infolog){
        $log=new LogResa();
        $log->setIdcustomer("non identifié");
        $log->setIdprovider($infolog['spaceweb']);
        $log->setEvent($infolog['event']);
        if($infolog['confirm']){
            $log->setConfirm(true);
        }else{
            $log->setConfirm(false);
            $log->setCodeerror($infolog['codeerror']);
            $log->setMessageerror($infolog['messageerror']);
        }
        $this->em->persist($log);
        $this->em->flush();
        return;
    }
}