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
    public function gallery(EntityManagerInterface $em, MobileLinkManager $qrBuilder): Response
    {
        $scenes = $em->getRepository(ArScene::class)->findBy([], ['createdAt' => 'DESC'], 100);
        $sceneCards = array_map(function (ArScene $scene) use ($qrBuilder) {
            return array_merge(
                ['scene' => $scene],
                $this->buildSharePayload($scene, $qrBuilder)
            );
        }, $scenes);

        return $this->renderAr('ar_mindar','_gallery', 1,[
            'sceneCards' => $sceneCards,
        ]);
    }

    #[Route('/ra/view/{id}', name: 'ar_view')]
    public function view(ArScene $scene, MobileLinkManager $qrBuilder): Response
    {
        return $this->renderAr('ar_mindar','_view',2, array_merge(
            ['scene' => $scene],
            $this->buildSharePayload($scene, $qrBuilder)
        ));
    }

    #[Route('/ra/experience/{token}', name: 'ar_scene_experience')]
    public function experience(EntityManagerInterface $em, MobileLinkManager $qrBuilder, string $token): Response
    {
        $scene = $em->getRepository(ArScene::class)->findOneBy(['shareToken' => $token]);
        if (!$scene) {
            throw $this->createNotFoundException('Scène RA introuvable.');
        }

        return $this->renderAr('ar_mindar', '_view', 2,array_merge(
            ['scene' => $scene],
            $this->buildSharePayload($scene, $qrBuilder)
        ));
    }

    #[Route('/ra/share/{token}', name: 'ar_scene_share')]
    public function share(EntityManagerInterface $em, MobileLinkManager $qrBuilder, string $token): Response
    {
        $scene = $em->getRepository(ArScene::class)->findOneBy(['shareToken' => $token]);
        if (!$scene) {
            throw $this->createNotFoundException('Scène RA introuvable.');
        }

        return $this->renderAr('ar_mindar', '_share',2, array_merge(
            ['scene' => $scene],
            $this->buildSharePayload($scene, $qrBuilder)
        ));
    }

    /**
     * @return array{shareUrl: string|null, experienceUrl: string|null, shareQr: string|null}
     */
    private function buildSharePayload(ArScene $scene, MobileLinkManager $qrBuilder): array
    {
        $token = $scene->getShareToken();
        if (!$token) {
            return [
                'shareUrl' => null,
                'experienceUrl' => null,
                'shareQr' => null,
            ];
        }


        $experienceUrl = $this->generateUrl('ar_scene_experience', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);


        return [
            'shareUrl' => $this->generateUrl('ar_scene_share', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL),
            'experienceUrl' => $experienceUrl,
            'shareQr' => $qrBuilder->buildQrForUrl($experienceUrl),
        ];
    }

    private function renderAr(string $directory, string $twig, int $h, array $payload = []): Response
    {

        $menuNav = $this->requireMenuNav();
        $vartwig = $menuNav->templatepotins($twig, Links::ACCUEIL);
        if ($h == 2) {
            return $this->render('pwa/ar/homeview.html.twig', array_merge([
                'directory' => $directory,
                'vartwig' => $vartwig,
            ], $payload));
        } else {
            return $this->render('pwa/ar/home.html.twig', array_merge([
                'directory' => $directory,
                'vartwig' => $vartwig,
            ], $payload));
        }
    }
}
