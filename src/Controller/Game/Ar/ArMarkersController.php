<?php

namespace App\Controller\Game\Ar;


use App\Service\MindArPackLocator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// (Option PDF) Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

class ArMarkersController extends AbstractController
{
    #[Route('/ra/markers/print/{pack}', name: 'ar_markers_print')]
    public function printMarkers(string $pack, MindArPackLocator $locator): Response
    {
        $found = $this->findPack($pack, $locator);
        $html = $this->renderView('pwa/ar/ar_mindar/print_markers.html.twig', [
            'pack' => $found,
            'eventTitle' => 'Atelier Réalité Augmentée — Quinzaine Zen',
            'siteUrl' => '/ra/mindar/demo'
        ]);
        return $this->maybePdf($html, "markers-{$found['name']}.pdf");
    }

    #[Route('/ra/markers/sheet/{pack}', name: 'ar_markers_sheet')]
    public function sheetMarkers(string $pack, MindArPackLocator $locator): Response
    {
        $found = $this->findPack($pack, $locator);
        $html = $this->renderView('pwa/ar/ar_mindar/print_markers_sheet.html.twig', [
            'pack' => $found,
            'eventTitle' => 'Atelier Réalité Augmentée — Quinzaine Zen',
            'siteUrl' => '/ra/mindar/demo',
            'perPage' => 4,
        ]);
        return $this->maybePdf($html, "markers-sheet-{$found['name']}.pdf");
    }

    private function findPack(string $pack, MindArPackLocator $locator): array
    {
        foreach ($locator->listPacks() as $p) {
            if ($p['name'] === $pack || str_contains($p['pathMind'], "/$pack/")) return $p;
        }
        throw $this->createNotFoundException("Pack introuvable : $pack");
    }

    private function maybePdf(string $html, string $filename): Response
    {
        $req = $this->container->get('request_stack')->getCurrentRequest();
        if (!$req->query->getBoolean('pdf')) return new Response($html);

        $options = new Options(); $options->set('isRemoteEnabled', true);
        $pdf = new Dompdf($options);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();
        return new Response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"'
        ]);
    }


}
