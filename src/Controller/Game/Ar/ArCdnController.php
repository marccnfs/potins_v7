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

    #[Route('/ra/lotus', name: 'ar_lotus')]
    public function lotus() {
        return $this->renderAr('ar_cdn','cdn',"lotus");
    }

    #[Route('/ra/createur', name: 'ar_createur')]
    public function createur() {

        $vartwig=$this->menuNav->templatepotins(
            '_createur',
            Links::ACCUEIL);

        return $this->render('pwa/ar_cdn/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'directory'=>'ar_cdn',
        ]);

    }

    private function renderAr(string $directory, string $twig, string $switch): Response
    {

        $menuNav = $this->requireMenuNav();

        $vartwig = $menuNav->templatepotins( $twig,Links::ACCUEIL);

        return $this->render( 'pwa/ar/home.html.twig',[
            'directory' => $directory,
            'vartwig' => $vartwig,
            'switch'=>$switch,
        ]);
    }

}
