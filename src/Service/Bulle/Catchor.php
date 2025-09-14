<?php


namespace App\Service\Bulle;


use App\Entity\UserMap\Catching;
use Doctrine\ORM\EntityManagerInterface;

class Catchor
{
    private $customer;
    private $where;
    private $bulle;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Resator constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

    }

    public function newCatching($parametre){  //['form'=>$form,'spaceweb'=>$spaceweb,'module'=>$module]
        $cathc=new Catching();
        $cathc->setWherecatching($parametre['space']);
        $cathc->setCustomercatch($parametre['customer']);
        $parametre['customer']->addCatching($cathc);
        $this->entityManager->persist($parametre['customer']);
        $this->entityManager->flush();
        return;
    }
}