<?php

namespace App\Controller\Game\Ar;

use App\Classe\UserSessionTrait;
use App\Lib\Links;
use App\Service\MindArPackLocator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArNpmController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/ra/mindar/demo', name: 'ar_mindar_demo')]
    public function demo(): Response
    {
        return $this->renderAr('ar_mindar','_demo',[]);
    }

    #[Route('/ra/mindar/create', name: 'ar_mindar_create')]
    public function create(MindArPackLocator $locator): Response
    {
        return $this->renderAr('ar_mindar','_create', [
            'packs' => $locator->getPacks(), // packs .mind pré-générés
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function renderAr(string $directory, string $twig, array $payload = []): Response
    {

        $menuNav = $this->requireMenuNav();
        $vartwig = $menuNav->templatepotins( $twig,Links::ACCUEIL);

        return $this->render( 'pwa/ar/home.html.twig',array_merge([
            'directory' => $directory,
            'vartwig' => $vartwig,
        ], $payload));

    }

}
