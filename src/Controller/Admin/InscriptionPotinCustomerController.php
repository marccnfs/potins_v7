<?php

namespace App\Controller\Admin;

use App\Classe\customersession;
use App\Entity\Admin\OrderProducts;
use App\Entity\Admin\Orders;
use App\Entity\Admin\PreOrderResa;
use App\Entity\Admin\WbOrderProducts;
use App\Entity\Admin\Wborders;
use App\Form\InscriptionPotinsType;
use App\Form\WbOrderProductType;
use App\Form\WorderPotinParticipantsType;
use App\Form\WOrderType;
use App\Lib\Links;
use App\Repository\OrdersRepository;
use App\Repository\PreOrderResaRepository;
use App\Repository\ProductsRepository;
use App\Repository\WbordersRepository;
use App\Service\Gestion\Commandar;
use App\Service\Gestion\Facturator;
use App\Service\Gestion\GetFacture;
use App\Service\Modules\Resator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[IsGranted('ROLE_CUSTOMER')]
#[Route(' /customer/gest-potins/')]

class InscriptionPotinCustomerController extends AbstractController
{
    use customersession;

    #[Route('cmd-potins/{id}', name:"back_admin_cmd_potins")]
    public function newInscriptionPotins($id, Request $request, Commandar $commandar, Resator $resator): Response
    {
        $event=$this->eventrepo->findEventById($id);
        $tabdatesevent=$resator->resapotin($event);
        $tabarray=$resator->CountSubByDateEvent($event);
        //todo test entre les deux tables pour savoir si une date est complete (rajouter dans l'event le nombre de particpant max

        $preO =New PreOrderResa();
        $form = $this->createForm(InscriptionPotinsType::class, $preO,
        ['listdate' => $tabdatesevent]);// todo a supprimer plus utile


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $preO->setCustomer($this->customer);
            $preO->setEvent($event);
            $preO=$resator->newPreOrderPotin($preO, $this->customer,$request->request->all()['inscription_potins'],$tabdatesevent); //limité à une date par commande pour l'instant
            $this->em->persist($preO);
            //$this->em->persist($event);
            $this->em->flush();
            return $this->redirectToRoute("valid_back_admin_cmd_potins",['id'=>$preO->getId()]);
        }
        $vartwig=$this->menuNav->templatepotins(
            Links::CUSTOMER_LIST,
            'Wadorder',
            "Wadorder",
            'all');

        return $this->render($this->useragentP.'ptn_customer/home.html.twig', [
            'directory'=>'orders',
            'form' => $form->createView(),
            'post'=>$event->getPotin(),
            'event'=>$event,
            'replacejs'=>$replace??null,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('valid-cmd-potins/{id}', name:"valid_back_admin_cmd_potins")]
    public function validInscriptionPotins($id, Request $request, Commandar $commandar, PreOrderResaRepository $preoderresarepo): Response
    {
        $preO=$preoderresarepo->findPreoAndJoin($id);
        $event=$preO->getEvent();
        $nbregistered=$preO->getNumberresa();
        $order =New Orders();
        $form = $this->createForm(WorderPotinParticipantsType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order=$commandar->newResaPotin($order,$preO);
            return $this->redirectToRoute("confirm_resa_potins",['id'=>$order->getId()]);
        }
        $vartwig=$this->menuNav->templatepotins(
            Links::CUSTOMER_LIST,
            'validpreorderesa',
            "validpreorderesa",
            'all');

        return $this->render($this->useragentP.'ptn_customer/home.html.twig', [
            'directory'=>'orders',
            'form' => $form->createView(),
            'event'=>$event,
            'preorder'=>$preO,
            'registered'=>$nbregistered,
            'replacejs'=>$replace??null,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig
        ]);
    }


    #[Route('confirm-resa-potins/{id}', name:"confirm_resa_potins")] //todo pour confirmation resa
    public function confirmInscriptionPotins($id, Request $request, Commandar $commandar, OrdersRepository $oderrepo): Response
    {
        $order=$oderrepo->findOrderEvent($id);
        $sub=$order->getListproducts()[0]->getSubscription();
        $event=$sub->getEvent();

        $vartwig=$this->menuNav->templatepotins(
            Links::CUSTOMER_LIST,
            'confirmresa',
            "confirmresa",
            'all');

        return $this->render($this->useragentP.'ptn_customer/home.html.twig', [
            'directory'=>'orders',
            'order'=>$order,
            'event'=>$event,
            'sub'=>$sub,
            'replacejs'=>$replace??null,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('board/{id}', name:"back_admin_gest_wbsite")]
    public function tabwbsiteBo( $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        $website=$this->wbrepo->findWebsiteAdmin($id);

        $vartwig=$this->menuNav->templateControl(
            Links::CUSTOMER_LIST,
            'website',
            "website",
            'all');

        return $this->render($this->useragentP.'ptn_customer/home.html.twig', [
            'directory'=>'website',
            'website'=>$website,
            'replacejs'=>$replace??null,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig
        ]);
    }


    #[Route('wbsite-liste-commandes/{id}', name:"back_admin_wbsite_commande_list")]
    public function listCommandeWebsiteAdmin( $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        $website=$this->wbrepo->findWebsiteAdmin($id);

        $vartwig=$this->menuNav->templateControl(
            Links::CUSTOMER_LIST,
            'listcommande',
            "website",
            'all');

        return $this->render('aff_master/home.html.twig', [
            'directory'=>'website',
            'website'=>$website,
            'replacejs'=>$replace??null,
            'customer'=>$this->dispatch,
            'vartwig'=>$vartwig
        ]);
    }



    #[Route('edit-command/{id}', name:"edit_command")]
    public function editCommand($id, Request $request, Commandar $commandar, WbordersRepository $wbordersRepository, ProductsRepository $productsRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        $order=$wbordersRepository->findAllOrder($id);
        $wbcli=$order->getWbcustomer();
        $website=$wbcli->getBoard();
        $form = $this->createForm(WOrderType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $commandar->editPrestaFree($order, $wbcli);
            return $this->redirectToRoute("back_admin_gest_wbsite",['id'=>$website->getId()]);
        }
        $vartwig=$this->menuNav->templateControl(
            Links::CUSTOMER_LIST,
            'Wadorder',
            "Wadorder",
            'all');

        return $this->render('aff_master/home.html.twig', [
            'directory'=>'orders',
            'form' => $form->createView(),
            'website'=>$website,
            'replacejs'=>$replace??null,
            'customer'=>$this->dispatch,
            'vartwig'=>$vartwig
        ]);
    }


    #[Route('cmd-module-gestionWb/{id}', name:"back_admin_cmd_module-gestionWb")]
    public function newBlokgestionWb($id, Request $request, Commandar $commandar, ProductsRepository $productsRepository ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        $website=$this->wbrepo->findForCmdById($id);
        $wbcli=$website->getWbcustomer();
        $orderprod=New OrderProducts();
        $form = $this->createForm(ResaPotinType::class, $orderprod);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $prod=$productsRepository->find(4);  // forfait 12 mois
            $order=$commandar->addprestaAffi($wbcli,$orderprod,$prod);
            return $this->redirectToRoute("back_admin_gest_wbsite",['id'=>$website->getId()]);
        }
        $vartwig=$this->menuNav->templateControl(
            Links::CUSTOMER_LIST,
            'addgestionorder',
            "addgestionorder",
            'all');
        return $this->render('aff_master/home.html.twig', [
            'directory'=>'orders',
            'form' => $form->createView(),
            'website'=>$website,
            'replacejs'=>$replace??null,
            'customer'=>$this->dispatch,
            'vartwig'=>$vartwig
        ]);
    }


    #[Route('cmd-module-coamine/{id}', name:"back_admin_cmd_module-domaine")]
    public function newBlokDomaine($id, Request $request, Commandar $commandar, ProductsRepository $productsRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        $website=$this->wbrepo->findForCmdById($id);
        $wbcli=$website->getWbcustomer();
        $orderprod=New WbOrderProducts();
        $form = $this->createForm(WbOrderProductType::class, $orderprod);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $prod=$productsRepository->find(4);
            $order=$commandar->addprestaAffi($wbcli,$orderprod,$prod);
            return $this->redirectToRoute("back_admin_gest_wbsite",['id'=>$website->getId()]);
        }
        $vartwig=$this->menuNav->templateControl(
            Links::CUSTOMER_LIST,
            'adddomaineorder',
            "adddomaineorder",
            'all');
        return $this->render('aff_master/home.html.twig', [
            'directory'=>'orders',
            'form' => $form->createView(),
            'website'=>$website,
            'replacejs'=>$replace??null,
            'customer'=>$this->dispatch,
            'vartwig'=>$vartwig
        ]);
    }


    #[Route('cmd-module-adword/{id}', name:"back_admin_cmd_module-adword")]
    public function newBlokadword($id, Request $request, Commandar $commandar,ProductsRepository $productsRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        $website=$this->wbrepo->findForCmdById($id);
        $wbcli=$website->getWbcustomer();
        $orderprod=New WbOrderProducts();
        $form = $this->createForm(WbOrderProductType::class, $orderprod);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $prod=$productsRepository->find(4);
            $order=$commandar->addprestaAffi($wbcli,$orderprod,$prod);
            return $this->redirectToRoute("back_admin_gest_wbsite",['id'=>$website->getId()]);
        }
        $vartwig=$this->menuNav->templateControl(
            Links::CUSTOMER_LIST,
            'addgestionorder',
            "addgestionorder",
            'all');
        return $this->render('aff_master/home.html.twig', [
            'directory'=>'orders',
            'form' => $form->createView(),
            'website'=>$website,
            'replacejs'=>$replace??null,
            'customer'=>$this->dispatch,
            'vartwig'=>$vartwig
        ]);
    }


    #[Route('cmd-module-show-commande-desk/{id}', name:"back_admin_show_commande-desk")]
    public function viewCommandedesk($id, WbordersRepository $wbordersRepository,Commandar $commandar, Facturator $facturator, GetFacture $getFacture, SpwsiteRepository $spwsiteRepository): Response
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
            'commandedesk',
            "commandedesk",
            'all');


        return $this->render('aff_master/home.html.twig', [
            'directory'=>'orders',
            'website'=>$website,
            'customer'=>$this->dispatch,
            'order'=> $order,
            'replacejs'=>$replace??null,
            'dispatch' =>$dispatch,
            'vartwig'=>$vartwig
        ]);
    }
}