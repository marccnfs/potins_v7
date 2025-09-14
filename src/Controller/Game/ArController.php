<?php

namespace App\Controller\Game;

use App\Classe\PublicSession;
use App\Lib\Links;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class ArController extends AbstractController
{
    use PublicSession;

    #[Route('/ra/intro', name: 'ar_intro')]
    public function intro(): Response
    {

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            '_intro',
            0,
            "nocity");


        return $this->render('pwa/ar/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'directory'=>'ar',
        ]);
    }


    #[Route('/ra/mindar/demo', name: 'ar_mindar_demo')]
    public function demo(): Response
    {

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            '_mindar_demo',
            0,
            "nocity");


        return $this->render('pwa/ar/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'directory'=>'ar',
        ]);
    }


    #[Route('/ra/mindar/create', name: 'ar_mindar_create')]
    public function create(): Response
    {


        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            '_mindar_create',
            0,
            "nocity");


        return $this->render('pwa/ar/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'directory'=>'ar',
        ]);
    }

}
