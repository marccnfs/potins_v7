<?php

namespace App\Classe;

use App\Entity\Member\Activmember;
use App\Entity\Boards\Board;
use App\Entity\Member\Boardslist;
use App\Repository\ActivMemberRepository;
use App\Repository\BoardRepository;
use App\Repository\BoardslistRepository;
use App\Service\MenuNavigator;
use App\Service\Registration\Sessioninit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;


trait adminsession
{
    private EntityManagerInterface $em;
    private ManagerRegistry $doctrine;
    private MenuNavigator $menuNav;
    private ActivMemberRepository $repodispacth;
    private BoardslistRepository $spwrepo;
    private BoardRepository $wbrepo;
    private Activmember $admin;
    private string $iddispatch;
    private Activmember $dispatch;
    private array $permission=[];
    private RequestStack $requestStack;
    private Security $security;
    private Sessioninit $sessioninit;


    public function __construct(Sessioninit $sessioninit, Security $security,EntityManagerInterface $em, RequestStack $requestStack, MenuNavigator $menuNav, BoardRepository $websiteRepository ,BoardslistRepository $spwsiteRepository, ActivMemberRepository $repodispatch)
    {
        $this->em = $em;
        $this->menuNav = $menuNav;
        $this->wbrepo = $websiteRepository;
        $this->spwrepo = $spwsiteRepository;
        $this->sessioninit = $sessioninit;
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->repodispacth = $repodispatch;
        $this->pregmatch();
       // $this->admin = $this->getadminWebsite();

        if ($this->security->isGranted("IS_AUTHENTICATED_REMEMBERED") && !$this->requestStack->getSession()->has('idcustomer')) $sessioninit->initCustomer($this->security->getUser());
        if ($this->requestStack->getSession()->has('idcustomer')) {
            $this->dispatch=$this->repodispacth->findwithidcustomerAll($this->requestStack->getSession()->get('idcustomer'));
            $this->requestStack->getSession()->set('init', true);
        }
    }

    protected function pregmatch(){
        if (preg_match('/mob/i', $_SERVER['HTTP_USER_AGENT'])) {
            $this->requestStack->getSession()->set('agent', 'mobile/');
        } else {
            $this->requestStack->getSession()->set('agent', 'desk/');
        }
    }

    public function getadminDispatch(): ?Activmember
    {
        return  $this->repodispacth->find(1);
    }

    public function getadminBoard(): ?Board
    {
        return   $this->wbrepo->find(2);
    }

    public function getadminPwsite(): ?Boardslist
    {
        return   $this->spwrepo->find(4);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getDispatchByEmail($email)
    {
        return $this->repodispacth->finddispatchmail($email);
    }

}