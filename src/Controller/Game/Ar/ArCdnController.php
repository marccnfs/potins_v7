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
        return $this->renderAr('ar_gen','_test0',0);
    }

    #[Route('/ra/test1', name: 'ar_test1')] // multi scene (ours et panda)
    public function test1(): Response
    {
        return $this->renderAr('ar_gen','_test1',0);
    }

    #[Route('/ra/minimal', name: 'ar_test2')]
    public function testMinimal(): Response
    {
        return $this->renderAr('ar_gen','_minimal',0);
    }

    #[Route('/ra/test3', name: 'ar_test3')]
    public function test3(): Response
    {
        return $this->renderAr('ar_gen','_test3',0);
    }

    #[Route('/ra/face-tracking-1', name: 'face_tracking_1')]
    public function faceTracking(): Response
    {
        return $this->renderAr('ar_gen','face_tracking_1',0);
    }

    #[Route('/ra/choice', name: 'ar_choice')]
    public function choice() {
        return $this->renderAr('ar_cdn','_choice',1);
    }

    #[Route('/ra/prepareur', name: 'ar_prepareur')]
    public function prepareur(): Response
    {
        return $this->renderAr('ar_cdn','_prepareur',0);
    }

    private function renderAr(string $directory, string $twig,int $switch): Response
    {
        $menuNav = $this->requireMenuNav();
        if($switch==0){
            return $this->render( "pwa/ar/ar_gen/".$twig.".html.twig");
        }
        else {
            $vartwig = $menuNav->templatepotins( $twig,Links::ACCUEIL);
            return $this->render( 'pwa/ar/homecdn.html.twig',[
                'directory' => $directory,
                'vartwig' => $vartwig,
                'switch' => $switch,
            ]);
        }
    }

}
