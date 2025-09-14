<?php

namespace App\Classe;


use App\Entity\Customer\Customers;
use App\Entity\Member\Activmember;
use App\Repository\CustomersRepository;
use App\Service\MenuNavigator;
use App\Service\Registration\Sessioninit;
use App\Service\Navigator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\SecurityBundle\Security;

trait potinsession
{
    private ObjectManager $em;
    private MenuNavigator $menuNav;
    private mixed $customerid;
    private array $locate;
    private RouterInterface $router;
    private Sessioninit $sessioninit;
    private Customers|null $customer;
    private Activmember|null $member;
    private Bool $useragent;
    private $useragentP;
    private RequestStack $requestStack;
    private array $listwb;
    private Security $security;
    private $navigator;



    public function __construct(Security $security,
                                EntityManagerInterface $em,
                                CustomersRepository $repocustomer,
                                RequestStack $requestStack,
                                MenuNavigator $menuNav,
                                RouterInterface $router,
                                Sessioninit $sessioninit,
                                Navigator $navigator)
    {
        $this->em = $em;
        $this->security = $security;
        $this->menuNav = $menuNav;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->sessioninit = $sessioninit;
        $this->customerid=null;
        $this->customer=null;
        $this->member=null;
        $this->pregmatch();
        $this->navigator = $navigator;

        if ($this->security->isGranted("IS_AUTHENTICATED_REMEMBERED")){
            $user=$this->security->getUser();
                $tabuser=$sessioninit->initCustomer($user);
                $this->customer=$tabuser['customer'];
                $this->member=$tabuser['member'];
        }else{
        $this->clearinit();
        }

    }

    protected function pregmatch()
    {
        if (preg_match('/mob/i', $_SERVER['HTTP_USER_AGENT'])) {
            $this->requestStack->getSession()->set('agent', 'pwa/');
            $this->useragent=false;
            $this->useragentP = "pwa/";
        } else {
            $this->requestStack->getSession()->set('agent', 'desk/');
            $this->useragent=true;
            $this->useragentP = "desk/";
        }
    }

    public function clearinit(){
        $this->requestStack->getSession()->remove('idcustomer');
        $this->customer=null;
        $this->member=null;
        $this->customerid=null;
    }

}