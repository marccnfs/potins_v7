<?php

namespace App\Module;

use App\Entity\Posts\Fiche;
use App\Entity\Posts\Post;
use App\Entity\Ressources\Reviews;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class GetReview extends AbstractController
{

    /**
     * @param Reviews $reviews
     * @param Fiche $fiche
     * @return Dompdf
     */
    public function newPdfReview(Reviews $reviews, Fiche $fiche, Post $post): Dompdf
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('/desk/ptn_office/review/ficheresumepourpdf.html.twig', [
            'review' => $reviews,
            'potin'=>$post
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        $publicDirectory = __DIR__ . '/../../public/review/pdf/'. $this->getParameter('path_review').'/datafiche';
        $namefile = '/fiche-'.$reviews->getTitre().'-'.'review'.'.pdf';
        $pdfFilepath = $publicDirectory . $namefile;
        file_put_contents($pdfFilepath, $output);
        $fiche->setPdfreview($namefile);
        return $dompdf;
    }

    public function miseAjPdfReview(Reviews $reviews, Fiche $fiche, Post $post): Dompdf
    {
        $publicDirectory = __DIR__ . '/../../public/review/pdf/'. $this->getParameter('path_review').'/datafiche';
        $pdf = $fiche->getPdfreview();
        $oldpdfFilepath = $publicDirectory . $pdf;
        if (file_exists($oldpdfFilepath)) {
            unlink($oldpdfFilepath);
        }
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($pdfOptions);

        $html = $this->renderView('/desk/ptn_office/review/ficheresumepourpdf.html.twig', [
            'review' => $reviews,
            'potin'=>$post
        ]);


        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();

        $namefile = '/fiche-'.$reviews->getTitre().'-'.'review'.'.pdf';
        $pdfFilepath = $publicDirectory . $namefile;
        file_put_contents($pdfFilepath, $output);
        $fiche->setPdfreview($namefile);
        return $dompdf;
    }

    public function deletePdfReview(Reviews $reviews, Fiche $fiche): Fiche
    {
        $publicDirectory = __DIR__ . '/../../../public/review/pdf/' . $this->getParameter('path_review') . '/datafiche';
        $pdf = $fiche->getPdfreview();
        $oldpdfFilepath = $publicDirectory . $pdf;
        if (file_exists($oldpdfFilepath)) {
            unlink($oldpdfFilepath);
        }
        $fiche->setPdfreview("");
        return $fiche;
    }

}

