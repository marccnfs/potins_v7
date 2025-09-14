<?php

namespace App\Classe;

use App\Entity\Customer\Customers;
use App\Entity\Sector\Gps;
use App\Repository\CustomersRepository;
use App\Service\MenuNavigator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;


trait initMember
{
    private EntityManagerInterface $em;
    private MenuNavigator $menuNav;
    private CustomersRepository $repocustomer;
    private Gps $locate;
    private mixed $customerid;
    private Customers $customer;
    private Security $security;
    private RequestStack $requestStack;
    private Bool $useragent;
    private string $useragentP;



    public function __construct(Security $security, CustomersRepository $repocustomer, EntityManagerInterface $em,RequestStack $requestStack, MenuNavigator $menuNav){
        $this->em = $em;
        $this->menuNav = $menuNav;
        $this->requestStack = $requestStack;
        $this->repocustomer = $repocustomer;
        $this->security = $security;
        $this->prepa();
        $this->clearinit();

        if ($this->security->isGranted("IS_AUTHENTICATED_REMEMBERED")){
            $this->customer=$repocustomer->find($this->security->getUser()->getCustomer());
        }
    }

    protected function prepa()
    {
        if (preg_match('/mob/i', $_SERVER['HTTP_USER_AGENT'])) {
            $this->requestStack->getSession()->set('agent', 'pwa/');
            $this->useragent = false;
            $this->useragentP = "pwa/";
        } else {
            $this->requestStack->getSession()->set('agent', 'desk/');
            $this->useragent = true;
            $this->useragentP = "desk/";
        }
    }

    public function clearinit(){
        $this->requestStack->getSession()->remove('idcustomer');
        $this->requestStack->getSession()->remove('typeuser');
    }
}