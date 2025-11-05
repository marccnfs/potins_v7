<?php

namespace App\Controller\Api;

use App\Entity\Games\ArScene;
use App\Service\MobileLinkManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


#[Route('/api/ar/scenes')]
class ArSceneController extends AbstractController
{
    #[Route('', name: 'api_ar_scene_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, MobileLinkManager $qrBuilder): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Payload JSON invalide.'], 400);
        }

        $assetUrl = $data['assetUrl'] ?? $data['modelUrl'] ?? null;
        if (!$assetUrl) {
            return $this->json(['error' => 'Aucun média sélectionné pour la scène.'], 400);
        }

        $contentType = $data['contentType'] ?? 'model';
        if (!in_array($contentType, ['model', 'video', 'image'], true)) {
            $contentType = 'model';
        }

        $transform = is_array($data['transform'] ?? null) ? $data['transform'] : [];
        $position = is_array($transform['position'] ?? null) ? $transform['position'] : [];
        $rotation = is_array($transform['rotation'] ?? null) ? $transform['rotation'] : [];
        $scale = is_array($transform['scale'] ?? null) ? $transform['scale'] : [];
        $scene = new ArScene();
        $scene->setTitle($data['title'] ?? 'Sans titre');
        $scene->setMindTargetPath($data['mindTargetPath'] ?? null);
        $scene->setTargetIndex((int) ($data['targetIndex'] ?? 0));
        $scene->setModelUrl($assetUrl);
        $scene->setContentType($contentType);
        $scene->setPositionX((float) ($position['x'] ?? 0));
        $scene->setPositionY((float) ($position['y'] ?? 0));
        $scene->setPositionZ((float) ($position['z'] ?? 0));
        $scene->setRotationX((float) ($rotation['x'] ?? 0));
        $scene->setRotationY((float) ($rotation['y'] ?? 0));
        $scene->setRotationZ((float) ($rotation['z'] ?? 0));
        $scene->setScaleX((float) ($scale['x'] ?? 1));
        $scene->setScaleY((float) ($scale['y'] ?? 1));
        $scene->setScaleZ((float) ($scale['z'] ?? 1));
        $scene->setSoundUrl($data['soundUrl'] ?? null);
        $scene->setOwnerId($this->getUser()?->getId());

        $em->persist($scene);
        $em->flush();

        $shareUrl = $this->generateUrl('ar_scene_share', ['token' => $scene->getShareToken()], UrlGeneratorInterface::ABSOLUTE_URL);
        $experienceUrl = $this->generateUrl('ar_scene_experience', ['token' => $scene->getShareToken()], UrlGeneratorInterface::ABSOLUTE_URL);
        $qr = $qrBuilder->buildQrForUrl($experienceUrl);

        return $this->json([
            'id' => $scene->getId(),
            'shareUrl' => $shareUrl,
            'experienceUrl' => $experienceUrl,
            'qr' => $qr,
        ], 201);
    }


    #[Route('', name: 'api_ar_scene_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $scenes = $em->getRepository(ArScene::class)->findBy([], ['createdAt' => 'DESC'], 50);
        return $this->json(array_map(fn (ArScene $scene) => [
            'id' => $scene->getId(),
            'title' => $scene->getTitle(),
            'mindTargetPath' => $scene->getMindTargetPath(),
            'targetIndex' => $scene->getTargetIndex(),
            'modelUrl' => $scene->getModelUrl(),
            'contentType' => $scene->getContentType(),
            'transform' => [
                'position' => [
                    'x' => $scene->getPositionX(),
                    'y' => $scene->getPositionY(),
                    'z' => $scene->getPositionZ(),
                ],
                'rotation' => [
                    'x' => $scene->getRotationX(),
                    'y' => $scene->getRotationY(),
                    'z' => $scene->getRotationZ(),
                ],
                'scale' => [
                    'x' => $scene->getScaleX(),
                    'y' => $scene->getScaleY(),
                    'z' => $scene->getScaleZ(),
                ],
            ],
            'soundUrl' => $scene->getSoundUrl(),
            'shareToken' => $scene->getShareToken(),
        ], $scenes));
    }
}
