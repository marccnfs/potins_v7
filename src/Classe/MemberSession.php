<?php

namespace App\Classe;


use App\Entity\Customer\Customers;
use App\Entity\Member\Activmember;
use App\Entity\Member\Boardslist;
use App\Entity\Boards\Board;
use App\Repository\ActivMemberRepository;
use App\Repository\BoardRepository;
use App\Repository\BoardslistRepository;
use App\Service\MenuNavigator;
use App\Service\Registration\Sessioninit;
use App\Service\Navigator;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\SecurityBundle\Security;


trait MemberSession
{
    private EntityManagerInterface $em;
    private MenuNavigator $menuNav;
    private Activmember|null $member;
    private Customers|null $customer;
    private Board|null $board;
    private Bool $useragent;
    private string $useragentP;
    private RequestStack $requestStack;
    private BoardRepository $repoBoard;
    private Security $security;
    private Navigator $navigator;
    private ActivMemberRepository $repomember;
    private RouterInterface $router;
    private Sessioninit $sessioninit;
    private BoardslistRepository $repolistboard;
    private Boardslist|collection $listboards;

    public function __construct(Security $security,
                                EntityManagerInterface $em,
                                RequestStack $requestStack,
                                MenuNavigator $menuNav,
                                RouterInterface $router,
                                ActivMemberRepository $repomember,
                                Sessioninit $sessioninit,
                                Navigator $navigator,
                                BoardslistRepository $repolistboard)
    {
        $this->em = $em;
        $this->security = $security;
        $this->menuNav = $menuNav;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->sessioninit = $sessioninit;
        $this->navigator = $navigator;
        $this->repomember = $repomember;
        $this->repolistboard=$repolistboard;
/*
        if ($this->security->isGranted("ROLE_MEMBER")){
            if($this->requestStack->getSession()->has('iddisptachweb')){
                $idmember=$this->requestStack->getSession()->get('iddisptachweb');
                $this->member=$this->repomember->findAllById($idmember);
                $this->customer=$this->member->getCustomer();
            }else{
                $this->sessioninit->InitCustomer($this->security->getUser());
            }
            $this->prepa();
        }elseif ($this->security->isGranted("ROLE_MEDIA")){
*/
        $this->clearinit();
        $tabuser=$this->sessioninit->InitCustomer($this->security->getUser());
        $this->member=$tabuser['member'];
        $this->customer=$this->member->getCustomer();
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

        $this->listboards=$this->member->getBoardslist();
        foreach ($this->member->getBoardslist() as $pw) {
            if ($pw->isIsdefault()){
                 $this->board = $pw->getBoard();
                 break;
            }
        }
        return $this->board;
    }

    public function clearinit(){
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