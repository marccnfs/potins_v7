<?php

namespace App\Controller\Game\Ar;


use App\Classe\UserSessionTrait;
use App\Lib\Links;
use App\Service\MindArPackLocator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ArController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/ra/test0', name: 'ar_test0')]
    public function test0(): Response
    {
        return $this->renderAr('ar_gen','_test',"0");
    }

    #[Route('/ra/test1', name: 'ar_test1')]
    public function test1(): Response
    {
        return $this->renderAr('ar_gen','_test',"1");
    }

    #[Route('/ra/test2', name: 'ar_test2')]
    public function test2(): Response
    {
        return $this->renderAr('ar_gen','_test',"2");
    }

    #[Route('/ra/test3', name: 'ar_test3')]
    public function test3(): Response
    {
        return $this->renderAr('ar_gen','_test',"3");
    }

    #[Route('/ra/brut', name: 'ar_brut')]
    public function brut(): Response
    {
        return $this->render( 'pwa/ar/ar_gen/brut.html');
    }

    #[Route('/ra/intro', name: 'ar_intro')]
    public function intro(): Response
    {
        return $this->renderAr('ar_gen','_intro',"");
    }

    #[Route('/ra/mindar/demo', name: 'ar_mindar_demo')]
    public function demo(): Response
    {
        return $this->renderAr('ar_mindar','_demo',"");
    }

    #[Route('/ra/mindar/create', name: 'ar_mindar_create')]
    public function create(MindArPackLocator $locator): Response
    {
        return $this->renderAr('ar_mindar','_create',"");
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function renderAr(string $directory, string $twig, string $test): Response
    {

        $menuNav = $this->requireMenuNav();

        $vartwig = $menuNav->templatepotins( $twig,Links::ACCUEIL);

        return $this->render( 'pwa/ar/home.html.twig',[
            'directory' => $directory,
            'replacejs' => false,
            'vartwig' => $vartwig,
            'member' => $this->currentMember,
            'customer' => $this->currentCustomer,
            'test'=>$test,
        ]);
    }

}
