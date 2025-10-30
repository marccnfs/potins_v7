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
        return $this->renderAr('ar_gen','_intro');
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
