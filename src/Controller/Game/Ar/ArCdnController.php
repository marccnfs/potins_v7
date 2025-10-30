<?php

namespace App\Controller\Game\Ar;


use App\Classe\UserSessionTrait;
use App\Lib\Links;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ArCdnController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/ra/test0', name: 'ar_test0')] // card simple avec gld
    public function test0(): Response
    {
        return $this->renderAr('ar_gen','_test0');
    }

    #[Route('/ra/test1', name: 'ar_test1')] // multi scene (ours et panda)
    public function test1(): Response
    {
        return $this->renderAr('ar_gen','_test1');
    }

    #[Route('/ra/minimal', name: 'ar_minimal')]
    public function testMinimal(): Response
    {
        return $this->renderAr('ar_gen','_minimal');
    }


    #[Route('/ra/lotus', name: 'ar_lotus')]
    public function lotus() {
        return $this->renderAr('ar_cdn','_lotus');
    }

    #[Route('/ra/prepareur', name: 'ar_prepareur')]
    public function prepareur(): Response
    {
        return $this->renderAr('ar_cdn','_prepareur');
    }

    private function renderAr(string $directory, string $twig): Response
    {

        $menuNav = $this->requireMenuNav();

        $vartwig = $menuNav->templatepotins( $twig,Links::ACCUEIL);

        return $this->render( 'pwa/ar/home.html.twig',[
            'directory' => $directory,
            'vartwig' => $vartwig,
        ]);
    }

}
