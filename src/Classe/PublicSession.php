<?php

namespace App\Classe;


use App\Entity\Users\Participant;
use App\Service\MenuNavigator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;


trait PublicSession
{
    private EntityManagerInterface $em;
    private MenuNavigator $menuNav;
    private Bool $useragent;
    private string $useragentP;
    private RequestStack $requestStack;
    private Security $security;
    private RouterInterface $router;


    public function __construct(Security $security,
                                EntityManagerInterface $em,
                                RequestStack $requestStack,
                                RouterInterface $router,
                                MenuNavigator $menuNav,
                                )
    {
        $this->em = $em;
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->menuNav = $menuNav;
        $this->prepa();
    }

    protected function prepa(): void
    {
       $session=$this->requestStack->getSession();
        if (preg_match('/mob/i', $_SERVER['HTTP_USER_AGENT'])) {
            $session->set('agent', 'pwa/');
            $this->useragent=false;
            $this->useragentP = "pwa/";
        } else {
            $session->set('agent', 'desk/');
            $this->useragent=true;
            $this->useragentP = "desk/";
        }
    }

    protected function currentParticipant(Request $request): Participant
    {
        $p=$request->attributes->get('_participant');
        if(!$p instanceof Participant) throw new \LogicException('No participant found - ou #[RequireParticipant] oublié');
        return $p;
    }

}
