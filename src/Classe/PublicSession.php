<?php

namespace App\Classe;

use App\Entity\Boards\Board;
use App\Entity\Customer\Customers;
use App\Entity\Member\Activmember;
use App\Entity\Sector\Gps;
use App\Entity\Users\Participant;
use App\Repository\ActivMemberRepository;
use App\Repository\BoardRepository;
use App\Repository\CustomersRepository;
use App\Service\MenuNavigator;
use App\Service\Navigator;
use App\Service\Registration\Sessioninit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

trait PublicSession
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
    private CustomersRepository $repocustomer;
    private BoardRepository $boardRepository;
    private RouterInterface $router;
    private Sessioninit $sessioninit;
    private Gps|null $city;

    public function __construct(Security $security,
                                EntityManagerInterface $em,
                                RequestStack $requestStack,
                                MenuNavigator $menuNav,
                                RouterInterface $router,
                                ActivMemberRepository $repomember,
                                CustomersRepository $repocustomer,
                                Sessioninit $sessioninit,
                                Navigator $navigator,
                                BoardRepository $boardRepository
                                )
    {
        $this->em = $em;
        $this->security = $security;
        $this->menuNav = $menuNav;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->sessioninit = $sessioninit;
        $this->navigator = $navigator;
        $this->repomember = $repomember;
        $this->repocustomer=$repocustomer;
        $this->boardRepository=$boardRepository;
        $this->clearinit();
        if ($this->security->isGranted("IS_AUTHENTICATED_REMEMBERED")){
            $user=$this->security->getUser();
            $tabuser=$sessioninit->initCustomer($user);
            $this->customer=$tabuser['customer'];
            $this->member=$tabuser['member'];
        }
        $this->prepa();
    }

    protected function prepa(): void
    {
        $this->board=$this->boardRepository->find(3);
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
        $this->member=null;
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

    protected function currentParticipant(Request $request): Participant
    {
        $p=$request->attributes->get('_participant');
        if(!$p instanceof Participant) throw new \LogicException('No participant found - ou #[RequireParticipant] oublié');
        return $p;
    }

}
