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

        $vartwig=$this->menuNav->templatepotins(
            '_lotus',
            Links::ACCUEIL);

        return $this->render('pwa/ar_cdn/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'directory'=>'ar_cdn',
        ]);

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

}
