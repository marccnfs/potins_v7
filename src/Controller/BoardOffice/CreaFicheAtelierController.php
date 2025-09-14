<?php


namespace App\Controller\BoardOffice;


use App\Classe\adminsession;
use App\Entity\Admin\Wborders;
use App\Lib\Links;
use App\Module\GetReview;
use App\Repository\BoardslistRepository;
use App\Repository\ReviewRepository;
use App\Repository\WbordersRepository;
use App\Service\Gestion\Commandar;
use App\Service\Gestion\Facturator;
use App\Service\Gestion\GetFacture;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/fiche-atelier/creator/')]

class CreaFicheAtelierController extends AbstractController{

    use adminsession;


    #[Route('/showReview-resume/{id}', name:"show_review_resume")]
    public function creaFicheResume( $id, ReviewRepository $reviewRepository, reviewpdftor $reviewpdftor, GetReview $getReview,)
        {
        $review=$reviewRepository->findForEdit($id);
        $dompdf=$reviewpdftor->newReviewResume($review);
        //$dompdf=$getReview->newpdfcmd($review,$dispatch);
        return $dompdf->stream();
    }


    #[Route('cmd-module-show-commande/{id}', name:"back_admin_show_commande")]
    public function viewCommande($id, WbordersRepository $wbordersRepository,Commandar $commandar, Facturator $facturator, GetFacture $getFacture, BoardslistRepository $spwsiteRepository){
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        /** @var Wborders $order */
        $order=$wbordersRepository->findAllOrderForCoammande($id);
        $website=$order->getWbcustomer()->getBoard();
        $spw=$spwsiteRepository->findadminwebsite($website);
        $dispatch=$spw[0]->getDisptachwebsite();
        $order=$commandar->calCmdWb($order);
        $dompdf=$getFacture->newpdfcmd($order,$dispatch);
        $dompdf -> stream ();
    }


    #[Route('cmd-module-show-commande-desk/{id}', name:"back_admin_show_commande-desk")]
    public function viewCommandedesk($id, WbordersRepository $wbordersRepository,Commandar $commandar, Facturator $facturator, GetFacture $getFacture, BoardslistRepository $spwsiteRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        /** @var Wborders $order */
        $order=$wbordersRepository->findAllOrderForCoammande($id);
        $spw=$spwsiteRepository->findadminwebsite($order->getWbcustomer()->getBoard());
        $dispatch=$spw[0]->getDisptachwebsite();
        $website=$order->getWbcustomer()->getBoard();
        $order=$commandar->calCmdWb($order);

        $vartwig=$this->menuNav->templateControl(
            Links::CUSTOMER_LIST,
            'view/commandedesk3', //todo ici test nouvlle page pour pdf
            "view/commandedesk",
            'all');

        return $this->render('aff_master/orders/pdfdesk.html.twig', [
            'directory'=>'orders',
            'website'=>$website,
            'customer'=>$this->dispatch,
            'order'=> $order,
            'dispatch' =>$dispatch,
            'vartwig'=>$vartwig
        ]);
    }

}