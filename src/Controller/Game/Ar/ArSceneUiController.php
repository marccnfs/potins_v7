<?php

namespace App\Controller\Game\Ar;

use App\Classe\UserSessionTrait;
use App\Entity\Games\ArScene;
use App\Lib\Links;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\MobileLinkManager;

class ArSceneUiController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/ra/gallery', name: 'ar_gallery')]
    public function gallery(EntityManagerInterface $em): Response
    {
        $scenes = $em->getRepository(ArScene::class)->findBy([], ['createdAt' => 'DESC'], 100);
        return $this->renderAr('ar_mindar','_gallery', ['scenes' => $scenes]);
    }

    #[Route('/ra/view/{id}', name: 'ar_view')]
    public function view(ArScene $scene): Response
    {
        return $this->renderAr('ar_mindar','_view', ['scene' => $scene]);
    }

    #[Route('/ra/experience/{token}', name: 'ar_scene_experience')]
    public function experience(EntityManagerInterface $em, string $token): Response
    {
        $scene = $em->getRepository(ArScene::class)->findOneBy(['shareToken' => $token]);
        if (!$scene) {
            throw $this->createNotFoundException('Scène RA introuvable.');
        }

        return $this->renderAr('ar_mindar', '_view', ['scene' => $scene]);
    }

    #[Route('/ra/share/{token}', name: 'ar_scene_share')]
    public function share(EntityManagerInterface $em, MobileLinkManager $qrBuilder, string $token): Response
    {
        $scene = $em->getRepository(ArScene::class)->findOneBy(['shareToken' => $token]);
        if (!$scene) {
            throw $this->createNotFoundException('Scène RA introuvable.');
        }

        $experienceUrl = $this->generateUrl('ar_scene_experience', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
        $shareQr = $qrBuilder->buildQrForUrl($experienceUrl);

        return $this->renderAr('ar_mindar', '_share', [
            'scene' => $scene,
            'experienceUrl' => $experienceUrl,
            'shareQr' => $shareQr,
        ]);
    }

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
