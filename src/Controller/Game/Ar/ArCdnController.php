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

    #[Route('/ra/plan-bleu', name: 'plan_bleu')] // card simple avec plan bleu
    public function planBleu(): Response
    {
        return $this->renderAr('ar_gen','planbleu',0);
    }

    #[Route('/ra/basic', name: 'basic')] // card potins
    public function basic(): Response
    {
        return $this->renderAr('ar_gen','basic',0);
    }

    #[Route('/ra/event-handing', name: 'event_handing')]
    public function eventHanding(): Response
    {
        return $this->renderAr('ar_gen','eventhanding',0);
    }

    #[Route('/ra/interactive', name: 'interactive')] // avec video et boutons interactifs
    public function interactive(): Response
    {
        return $this->renderAr('ar_gen','_test3',0);
    }

    #[Route('/ra/face-tracking-1', name: 'face_tracking_1')]
    public function faceTracking(): Response
    {
        return $this->renderAr('ar_gen','face_tracking_1',0);
    }

    #[Route('/ra/face-tracking-2', name: 'face_tracking_2')]
    public function faceTracking2(): Response
    {
        return $this->renderAr('ar_gen','face_tracking_2',0);
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
