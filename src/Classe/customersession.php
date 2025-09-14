<?php

namespace App\Classe;

use App\Entity\Customer\Customers;
use App\Entity\Boards\Board;
use App\Entity\Member\Activmember;
use App\Repository\ActivMemberRepository;
use App\Repository\BoardRepository;
use App\Repository\BoardslistRepository;
use App\Repository\PostEventRepository;
use App\Service\MenuNavigator;
use App\Service\Registration\Sessioninit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\SecurityBundle\Security;


trait customersession
{

    private mixed $useragent;
    private EntityManagerInterface $em;
    private MenuNavigator $menuNav;
    private ActivMemberRepository $repodispacth;
    private BoardRepository $boardrepo;
    private BoardslistRepository $bdlrepo;
    private PostEventRepository $eventrepo;
    private Security $security;
    private Board|null $board;
    private RouterInterface $router;
    private Sessioninit $sessioninit;
    private Customers|null $customer;
    private RequestStack $requestStack;
    private Activmember|null $member;
    private string $useragentP;


    public function __construct(
                        PostEventRepository $eventRepository,
                        Sessioninit $sessioninit,
                        Security $security,
                        EntityManagerInterface $em,
                        RequestStack $requestStack,
                        MenuNavigator $menuNav,
                        ActivMemberRepository $repodispatch,
                        BoardRepository $boardRepository,
                        BoardslistRepository $boardlistRepository)
                    {

        $this->em = $em;
        $this->menuNav = $menuNav;
        $this->sessioninit = $sessioninit;
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->repodispacth = $repodispatch;
        $this->boardrepo = $boardRepository;
        $this->bdlrepo = $boardlistRepository;
        $this->eventrepo=$eventRepository;

        if ($this->security->isGranted("IS_AUTHENTICATED_REMEMBERED")){
            $user=$this->security->getUser();
            $tabuser=$sessioninit->initCustomer($user);
            $this->customer=$tabuser['customer'];
            $this->member=$tabuser['member'];
        }else{
            $this->clearinit();
        }
        $this->prepa();
        }

    protected function prepa()
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
        $this->customer=null;
        $session=$this->requestStack->getSession();
        if($session->has('city'))$session->remove('city');
        if($session->has('idcustomer'))$session->remove('idcustomer');
        if($session->has('lat'))$session->remove('lat');
        if($session->has('lon'))$session->remove('lon');
        if($session->has('iddisptachweb'))$session->remove('iddisptachweb');
        if($session->has('permission'))$session->remove('permission');
        if($session->has('idcity'))$session->remove('idcity');
    }

}