<?php


namespace App\Service\Registration;

use App\Entity\Customer\Customers;
use App\Repository\UserRepository;
use App\Service\Gestion\AutoCommande;
use App\Service\Gestion\Numerator;
use Doctrine\ORM\NonUniqueResultException;
use Exception;

class Identificat
{

    private CreatorUser $creator;
    private Numerator $numerator;
    private AutoCommande $autoCommande;
    private UserRepository $userrepo;

    public function __construct(UserRepository $userrepo,Numerator $numerator, CreatorUser $creator,AutoCommande $autoCommande)
    {
        $this->creator=$creator;
        $this->numerator=$numerator;
        $this->autoCommande=$autoCommande;
        $this->userrepo=$userrepo;
    }

    /**
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function creatorusager($user, $form): void
    {
        $nums=$this->numerator->getActiveNumeratecustomer();
        $customer=$this->creator->inituser($user, $form, $nums);
        $this->autoCommande->newInscriptionCmd($customer->getNumclient());
    }

    public function creatorContactResa($form): ?Customers
    {
        if(!$user=$this->userrepo->findOneBy(['email'=>$form['email']->getData()])){
        $nums=$this->numerator->getActiveNumeratecustomer();
        $customer=$this->creator->createContactByMedia($form, $nums);
        $this->autoCommande->newInscriptionCmd($customer->getNumclient());
        }else{
            $customer=$user->getCustomer();
        }
        return $customer;
    }

    /**
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function creatormember($user, $form){
        $nums=$this->numerator->getActiveNumeratecustomer();
        $customer=$this->creator->initmember($user, $form, $nums);
        $this->autoCommande->newInscriptionCmd($customer->getNumclient());
    }


    /* desactive pour l'instant
    public function creatorMemberPro($user, $form){
        $nums=$this->numerator->getActiveNumeratecustomer();
        $customer=$this->creator->initMemberPro($user, $form, $nums);
        $this->autoCommande->newInscriptionCmd($customer->getNumclient());
    }
    */

    public function creatorMediatheque($user, $form){
        $nums=$this->numerator->getActiveNumeratecustomer();
        $customer=$this->creator->initMediatheque($user, $form, $nums);
        $this->autoCommande->newInscriptionCmd($customer->getNumclient());
    }
}