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
    #[Route('/ra/markers/printv1/{pack}', name: 'ar_markers_printv1')]
    public function printMarkersv1(string $pack, MindArPackLocator $locator): Response
    {
        $packs = $locator->listPacks();
        $found = null;
        foreach ($packs as $p) {
            // on matche sur le nom ou sur le chemin .mind (souple)
            if ($p['name'] === $pack || str_contains($p['pathMind'], "/$pack/")) {
                $found = $p; break;
            }
        }
        if (!$found) {
            throw $this->createNotFoundException("Pack introuvable : $pack");
        }

        // Render HTML (pour impression navigateur OU PDF)
        $html = $this->renderView('ar/print_markers.html.twig', [
            'pack' => $found, // ['name','pathMind','items'=>[{index,label,thumb},..]]
            'eventTitle' => 'Atelier Réalité Augmentée — Quinzaine Zen',
            'siteUrl' => $this->generateUrl('ar_mindar_test_multi', [], 0), // adapter si besoin
        ]);

        // ---- MODE HTML (par défaut) ----
        if (!$this->get('request_stack')->getCurrentRequest()->query->getBoolean('pdf')) {
            return new Response($html);
        }

        // ---- MODE PDF (Dompdf) ----
        $options = new Options();
        $options->set('isRemoteEnabled', true); // autorise images http(s)
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="markers-'.$pack.'.pdf"'
            ]
        );
    }

    #[Route('/ra/markers/print/{pack}', name: 'ar_markers_print')]
    public function printMarkers(string $pack, MindArPackLocator $locator): Response
    {
        $found = $this->findPack($pack, $locator);
        $html = $this->renderView('ar/print_markers.html.twig', [
            'pack' => $found,
            'eventTitle' => 'Atelier Réalité Augmentée — Quinzaine Zen',
            'siteUrl' => $this->generateUrl('ar_mindar_test_multi', [], 0),
        ]);
        if (!$this->requestIsPdf()) return new Response($html);

        return $this->renderPdf($html, "markers-{$found['name']}.pdf");
    }

    #[Route('/ra/markers/sheet/{pack}', name: 'ar_markers_sheet')]
    public function sheetMarkers(string $pack, MindArPackLocator $locator): Response
    {
        $found = $this->findPack($pack, $locator);
        $html = $this->renderView('ar/print_markers_sheet.html.twig', [
            'pack' => $found,
            'eventTitle' => 'Atelier Réalité Augmentée — Quinzaine Zen',
            'siteUrl' => $this->generateUrl('ar_mindar_test_multi', [], 0),
            'perPage' => 4,   // 2x2 par page
        ]);
        if (!$this->requestIsPdf()) return new Response($html);

        return $this->renderPdf($html, "markers-sheet-{$found['name']}.pdf");
    }

    private function findPack(string $pack, MindArPackLocator $locator): array
    {
        $packs = $locator->listPacks();
        foreach ($packs as $p) {
            if ($p['name'] === $pack || str_contains($p['pathMind'], "/$pack/")) return $p;
        }
        throw $this->createNotFoundException("Pack introuvable : $pack");
    }

    private function requestIsPdf(): bool
    {
        return $this->get('request_stack')->getCurrentRequest()->query->getBoolean('pdf');
    }

    private function renderPdf(string $html, string $filename): Response
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"'
        ]);
    }



}
