<?php


namespace App\Classe;


use App\Service\MenuNavigator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;


trait sessionConnect
{
    private string $useragent;
    private EntityManagerInterface $em;
    private MenuNavigator $menuNav;
    private RequestStack $requestStack;
    private $session;



    public function __construct(EntityManagerInterface $em, MenuNavigator $menuNav, RequestStack $requestStack){
        $this->requestStack=$requestStack;
        $this->session = $this->requestStack->getSession();
        $this->menuNav=$menuNav;
        $this->em = $em;
        $this->pregmatch();
    }
    protected function pregmatch(){
        if (preg_match('/mob/i', $_SERVER['HTTP_USER_AGENT'])) {
            $this->session->set('agent', 'mobile/');
            $this->useragent=false;
           // $this->useragent = "mobile/";
        } else {
            $this->session->set('agent', 'desk/');
            $this->useragent=true;
           // $this->useragentP = "desk/";
        }
    }
}