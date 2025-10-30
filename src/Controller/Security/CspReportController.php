<?php

namespace App\Controller\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CspReportController extends AbstractController
{
    #[Route('/csp-report', name: 'csp_report', methods: ['POST'])]
    public function report(Request $request): Response
    {
        file_put_contents(
            $this->getParameter('kernel.logs_dir').'/csp_report.log',
            $request->getContent()."\n",
            FILE_APPEND
        );
        return new Response('', 204);
    }
}
