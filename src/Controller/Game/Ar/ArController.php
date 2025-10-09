<?php

namespace App\Controller\Game\Ar;


use App\Classe\UserSessionTrait;
use App\Lib\Links;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ArController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/ra/intro', name: 'ar_intro')]
    public function intro(): Response
    {

        $vartwig=$this->menuNav->templatepotins(
            '_intro',
            Links::ACCUEIL);


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
            '_mindar_demo',
            Links::ACCUEIL);

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
            '_mindar_create',
            Links::ACCUEIL);

        return $this->render('pwa/ar/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'directory'=>'ar',
        ]);
    }

}
