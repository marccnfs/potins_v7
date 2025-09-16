<?php

namespace App\Controller\Admin;

use App\Classe\MemberSession;
use App\Entity\Admin\Orders;
use App\Entity\Admin\PreOrderResa;
use App\Form\DeleteType;
use App\Repository\RegisteredRepository;
use App\Form\InscriptionPotinsMediaType;
use App\Form\WorderPotinParticipantsType;
use App\Lib\Links;
use App\Repository\OrdersRepository;
use App\Repository\PostEventRepository;
use App\Repository\PreOrderResaRepository;
use App\Service\Gestion\Commandar;
use App\Service\Modules\Resator;
use App\Service\Registration\Identificat;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_MEMBER')]
#[Route('/media/gest-potins')]

class InscriptionPotinByAdminController extends AbstractController
{
    use MemberSession;

    #[Route('resa-potins-admin/{id}/{date}', name:"resa_potins_admin")]
    public function newInscriptionPotinsByMedia($id, $date, Request $request, PostEventRepository $eventrepo, Resator $resator, Identificat $identificator,): Response
    {
        $event=$eventrepo->findEventById($id);
        //$date=$event->getAppointment()->getStarttime()->getTimestamp();

        $preO =New PreOrderResa();

        $form = $this->createForm(InscriptionPotinsMediaType::class, $preO,[]);// todo a supprimer plus utile
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $C_customer=$identificator->creatorContactResa($form);
            $preO->setCustomer($C_customer);
            $preO->setEvent($event);
            $preO=$resator->newPreOrderMediaPotin($preO, $request->request->all()['inscription_potins_media'],$date); //limité à une date par commande pour l'instant
            $this->em->persist($preO);
            $this->em->flush();
            return $this->redirectToRoute("valid_resa_admin_potins",['id'=>$preO->getId()]);
        }

        $vartwig=$this->menuNav->templatingadmin(
            'add_part',
            $this->board->getNameboard(),
            $this->board,
            3
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'event',
            'form' => $form->createView(),
            'post'=>$event->getPotin(),
            'event'=>$event,
            'replacejs'=>$replace??null,
            'vartwig'=>$vartwig,
            'board' => $this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
        ]);
    }

    #[Route('valid-potins-admin/{id}', name:"valid_resa_admin_potins")]
    public function validInscriptionPotins($id, Request $request, Commandar $commandar, PreOrderResaRepository $preoderresarepo): Response
    {
        $preO=$preoderresarepo->findPreoAndJoin($id);
        $event=$preO->getEvent();
        $nbregistered=$preO->getNumberresa();

        $order =New Orders();
        $form = $this->createForm(WorderPotinParticipantsType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if($commandar->newResaPotin($order,$preO)){
                return $this->redirectToRoute("details_event",['id'=>$event->getId()]);
            }else {
                return $this->redirectToRoute("echec_resa_admin_potins", ['id' => $order->getId()]);
            }
        }

        $vartwig=$this->menuNav->templatingadmin(
            'validpreorderesaadmin',
            $this->board->getNameboard(),
            $this->board,
            3
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'event',
            'form' => $form->createView(),
            'event'=>$event,
            'preorder'=>$preO,
            'registered'=>$nbregistered,
            'replacejs'=>$replace??null,
            'board' => $this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig
        ]);
    }


    #[Route('confirm-potins-admin/{id}', name:"confirm_resa_admin_potins")] //todo pour confirmation resa
    public function confirmInscriptionPotins($id, Request $request, Commandar $commandar, OrdersRepository $oderrepo): Response
    {
        $order=$oderrepo->findOrderEvent($id);
        $sub=$order->getListproducts()[0]->getSubscription();
        $event=$sub->getEvent();

        $vartwig=$this->menuNav->templatingadmin(
            'confirmresa',
            $this->board->getNameboard(),
            $this->board,
            3
        );

        return $this->render($this->useragentP.'ptn_media/home.html.twig', [
            'directory'=>'resa',
            'order'=>$order,
            'event'=>$event,
            'sub'=>$sub,
            'replacejs'=>$replace??null,
            'board' => $this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('Echec-resa-potins-admin/{id}', name:"echec_resa_admin_potins")] //todo pour confirmation resa
    public function echecInscriptionPotins($id, Request $request, Commandar $commandar, OrdersRepository $oderrepo): Response
    {
        $order=$oderrepo->findOrderEvent($id);
        $sub=$order->getListproducts()[0]->getSubscription();
        $event=$sub->getEvent();

        $vartwig=$this->menuNav->templatingadmin(
            'confirmresa',
            $this->board->getNameboard(),
            $this->board,
            3
        );
        return $this->render($this->useragentP.'ptn_media/home.html.twig', [
            'directory'=>'resa',
            'order'=>$order,
            'event'=>$event,
            'sub'=>$sub,
            'replacejs'=>$replace??null,
            'board' => $this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig
        ]);
    }


    #[Route('edit-info-usager-registered-admin/{idorder}/{id}]', name:"edit_registered_admin")]
    public function editOrderRegisterd(RegisteredRepository $regisrepo,OrdersRepository $ordersRepository,Commandar $commandar,Request $request,$id,$idorder): Response
    {
        $order=$ordersRepository->findOrderEvent($idorder);
        $orginalListproduc=new ArrayCollection();
        foreach ($order->getListproducts() as $product){
            $orginalListproduc->add($product);
        }

        $event=$order->getListproducts()[0]->getSubscription()->getEvent();
        $form = $this->createForm(WorderPotinParticipantsType::class, $order);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
           // dump($form->getData());
            $commandar->MajResaPotin($form->getData(),$orginalListproduc);
            return $this->redirectToRoute("office_media");
        }

        $vartwig=$this->menuNav->templatingadmin(
            'editorderesa',
            $this->board->getNameboard(),
            $this->board,
            3
        );
        return $this->render($this->useragentP.'ptn_media/home.html.twig', [
            'directory'=>'resa',
            'form' => $form->createView(),
            'event'=>$event,
            'order'=>$order,
            'replacejs'=>$replace??null,
            'board' => $this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('/form-delete-resa-admin/{idorder}/{id}', name:"form-delete_resa_admin")]
    public function deleteResaAdmin(RegisteredRepository $regisrepo,OrdersRepository $ordersRepository,Commandar $commandar,Request $request,$id,$idorder): RedirectResponse|Response
    {

        $order=$ordersRepository->findOrderEvent($idorder);
        $orginalListproduc=new ArrayCollection();
        foreach ($order->getListproducts() as $product){
            $orginalListproduc->add($product);
        }

        $event=$order->getListproducts()[0]->getSubscription()->getEvent();
        $form = $this->createForm(DeleteType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $commandar->deleteParticpant($order);
            return $this->redirectToRoute('office_media');
        }

        $vartwig=$this->menuNav->templatingadmin(
            'deleteorderesa',
            $this->board->getNameboard(),
            $this->board,
            3
        );

        return $this->render($this->useragentP.'ptn_media/home.html.twig', [
            'directory'=>'resa',
            'form' => $form->createView(),
            'replacejs'=>$replace??null,
            'board' => $this->board,
            'event'=>$event,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'vartwig' => $vartwig,
        ]);
    }

}
