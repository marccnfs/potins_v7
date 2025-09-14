<?php

namespace App\Controller\Api;

use App\Entity\Games\ArScene;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/api/ar/scenes')]
class ArSceneController extends AbstractController
{
    #[Route('', name: 'api_ar_scene_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $scene = new ArScene();
        $scene->setTitle($data['title'] ?? 'Sans titre');
        $scene->setMindTargetPath($data['mindTargetPath'] ?? null);
        $scene->setTargetIndex((int)($data['targetIndex'] ?? 0));
        $scene->setModelUrl($data['modelUrl']);
        $scene->setSoundUrl($data['soundUrl'] ?? null);
        $scene->setOwnerId($this->getUser()?->getId());
        $em->persist($scene);
        $em->flush();
        return $this->json(['id' => $scene->getId()], 201);
    }


    #[Route('', name: 'api_ar_scene_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $scenes = $em->getRepository(ArScene::class)->findBy([], ['createdAt' => 'DESC'], 50);
        return $this->json(array_map(fn(ArScene $s) => [
            'id' => $s->getId(),
            'title' => $s->getTitle(),
            'mindTargetPath' => $s->getMindTargetPath(),
            'targetIndex' => $s->getTargetIndex(),
            'modelUrl' => $s->getModelUrl(),
            'soundUrl' => $s->getSoundUrl(),
        ], $scenes));
    }
}
