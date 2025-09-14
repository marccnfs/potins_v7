<?php

namespace App\Service\Gestion;

use App\Entity\Admin\Factures;
use App\Entity\Admin\FacturesCustomer;
use App\Entity\Admin\Orders;
use App\Entity\Admin\Wborders;
use App\Entity\Member\Activmember;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class GetFacture extends AbstractController
{

    /**
     * @param $facture Factures
     * @param $order Wborders
     * @param $dispatch
     * @return Dompdf
     */
    public function newpdffacture(Factures $facture, Wborders $order, $dispatch): Dompdf
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('aff_master/admin/view/Wfacture3.html.twig', [
            'order'=> $order,
            'facture'=>$facture,
            'dispatch' =>$dispatch
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        $publicDirectory =  __DIR__.'/../../../public/wb-v5/gest/'.$this->getParameter('path_facture').'/admin-/pdf';
        $namefile='/f-'.$facture->getCreateAt()->format("Y-m-d").'-'.$order->getId().'.pdf';
        $pdfFilepath =  $publicDirectory .$namefile;
        file_put_contents($pdfFilepath, $output);
        $facture->setPdfacture($namefile);
        return $dompdf;
    }

    public function miseAjPdffacture($facture, $order, $dispatch ): Dompdf
    {
        $publicDirectory =  __DIR__.'/../../../public/wb-v5/gest/'.$this->getParameter('path_facture').'/admin-/pdf';
        $pdf=$facture->getPdfacture();
        $oldpdfFilepath =  $publicDirectory.$pdf;
        if(file_exists($oldpdfFilepath)){
            unlink($oldpdfFilepath);
        }
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('aff_master/admin/view/Wfacture3.html.twig', [
            'order'=> $order,
            'facture'=>$facture,
            'dispatch' =>$dispatch
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();

        $namefile='/f-'.$facture->getCreateAt()->format("Y-m-d").'-'.$order->getId().'.pdf';
        $pdfFilepath =  $publicDirectory .$namefile;
        file_put_contents($pdfFilepath, $output);
        $facture->setPdfacture($namefile);
        return $dompdf;
    }

    /**
     * @param $facture Factures
     * @param $order Wborders
     * @param $dispatch
     * @return Dompdf
     */
    public function showpdffacture(Factures $facture, Wborders $order, $dispatch): Dompdf
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('aff_master/admin/view/Wfacture3.html.twig', [
            'order'=> $order,
            'facture'=>$facture,
            'dispatch' =>$dispatch
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        $publicDirectory =  __DIR__.'/../../../public/wb-v5/gest/'.$this->getParameter('path_facture').'/admin-/pdf';
        $namefile='/f-'.$facture->getCreateAt()->format("Y-m-d").'-'.$order->getId().'.pdf';
        $pdfFilepath =  $publicDirectory .$namefile;
        file_put_contents($pdfFilepath, $output);
        $facture->setPdfacture($namefile);
        return $dompdf;
    }

    /**
     * @param $order Wborders
     * @param $dispatch
     * @return Dompdf
     */
    public function newpdfcmd(Wborders $order, $dispatch): Dompdf
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('aff_master/orders/view/commande.html.twig', [
            'order'=> $order,
            'dispatch' =>$dispatch
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf;
    }


    /*-------------------------- les facture customers ------------------------*/

    /**
     * @param $facture FacturesCustomer
     * @param $order Orders
     * @param Activmember $dispatch
     * @return Dompdf
     */
    public function newpdffactureCustomer(FacturesCustomer $facture, Orders $order,Activmember $dispatch): Dompdf
    {
        dump($order);
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('aff_account/gestion/view/Wfacture3.html.twig', [
            'order'=> $order,
            'facture'=>$facture,
            'dispatch' =>$dispatch
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        $publicDirectory =  __DIR__.'/../../../public/wb-v5/gest/'.$this->getParameter('path_facture').'/admin-/pdf';
        $namefile='/f-'.$facture->getCreateAt()->format("Y-m-d").'-'.$order->getId().'.pdf';
        $pdfFilepath =  $publicDirectory .$namefile;
        file_put_contents($pdfFilepath, $output);
        $facture->setPdfacture($namefile);
        return $dompdf;
    }

    /**
     * @param $facture FacturesCustomer
     * @param $order Orders
     * @param Activmember $dispatch
     * @return Dompdf
     */
    public function miseAjPdffactureCustomer(FacturesCustomer $facture, Orders $order, Activmember $dispatch): Dompdf
    {
        $publicDirectory =  __DIR__.'/../../../public/wb-v5/gest/'.$this->getParameter('path_facture').'/admin-/pdf';
        $pdf=$facture->getPdfacture();
        $oldpdfFilepath =  $publicDirectory.$pdf;
        if(file_exists($oldpdfFilepath)){
            unlink($oldpdfFilepath);
        }
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('aff_account/gestion/view/Wfacture3.html.twig', [
            'order'=> $order,
            'facture'=>$facture,
            'dispatch' =>$dispatch
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();

        $namefile='/f-'.$facture->getCreateAt()->format("Y-m-d").'-'.$order->getId().'.pdf';
        $pdfFilepath =  $publicDirectory .$namefile;
        file_put_contents($pdfFilepath, $output);
        $facture->setPdfacture($namefile);
        return $dompdf;
    }

    /**
     * @param FacturesCustomer $facture Factures
     * @param Orders $order Wborders
     * @param Activmember $dispatch
     * @return Dompdf
     */
    public function showpdffactureCustomer(FacturesCustomer $facture, Orders $order, Activmember $dispatch): Dompdf
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('aff_account/gestion/view/Wfacture3.html.twig', [
            'order'=> $order,
            'facture'=>$facture,
            'dispatch' =>$dispatch
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        $publicDirectory =  __DIR__.'/../../../public/wb-v5/gest/'.$this->getParameter('path_facture').'/admin-/pdf';
        $namefile='/f-'.$facture->getCreateAt()->format("Y-m-d").'-'.$order->getId().'.pdf';
        $pdfFilepath =  $publicDirectory .$namefile;
        file_put_contents($pdfFilepath, $output);
        $facture->setPdfacture($namefile);
        return $dompdf;
    }


    public function newpdfcmdCustomer(Orders $order, Activmember $dispatch): Dompdf
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('aff_account/gestion/view/commande.html.twig', [
            'order'=> $order,
            'dispatch' =>$dispatch
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf;
    }
}