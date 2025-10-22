<?php

namespace App\Controller\Game\Ar;

use App\Entity\Games\ArScene;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ArSceneUiController extends AbstractController
{
    #[Route('/ra/gallery', name: 'ar_gallery')]
    public function gallery(EntityManagerInterface $em): Response
    {
        $scenes = $em->getRepository(ArScene::class)->findBy([], ['createdAt' => 'DESC'], 100);
        return $this->render('ar_mindar/gallery.html.twig', ['scenes' => $scenes]);
    }

    #[Route('/ra/view/{id}', name: 'ar_view')]
    public function view(ArScene $scene): Response
    {
        return $this->render('ar_mindar/view.html.twig', ['scene' => $scene]);
    }
}
