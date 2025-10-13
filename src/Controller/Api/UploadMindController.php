<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/upload')]
class UploadMindController extends AbstractController
{
    #[Route('/mind', name: 'api_upload_mind', methods: ['POST'])]
    public function mind(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file) return $this->json(['error' => 'no file'], 400);

        if ($file->getClientOriginalExtension() !== 'mind') {
            return $this->json(['error' => 'invalid ext'], 400);
        }

        $targetDir = $this->getParameter('kernel.project_dir').'/public/uploads/mind';
        @mkdir($targetDir, 0775, true);

        $safeName = 'targets_'.time().'.mind';
        $file->move($targetDir, $safeName);

        return $this->json(['path' => '/uploads/mind/'.$safeName], 201);
    }
}
