<?php

namespace App\Controller\Mediatheques;

use App\Classe\UserSessionTrait;
use App\Entity\Admin\Orders;
use App\Entity\Admin\PreOrderResa;
use App\Form\DeleteType;
use App\Form\InscriptionPotinsMediaType;
use App\Form\ProfilresaType;
use App\Form\RegisteredonlyType;
use App\Form\WorderPotinParticipantsType;
use App\Lib\Links;
use App\Repository\CustomersRepository;
use App\Repository\OrdersRepository;
use App\Repository\PostEventRepository;
use App\Repository\PreOrderResaRepository;
use App\Repository\RegisteredRepository;
use App\Service\Gestion\Commandar;
use App\Service\Modules\Resator;
use App\Service\Registration\CreatorUser;
use App\Service\Registration\Identificat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_MEDIA')]
#[Route('/media/gest-potins/')]

class InscriptionPotinByMediaController extends AbstractController
{
    use UserSessionTrait;

    #[Route('resa-potins-media/{id}/{date}', name:"resa_potins_media")]
    public function newInscriptionPotinsByMedia($id,$date, Request $request, PostEventRepository $eventrepo, Resator $resator, Identificat $identificator,): Response
    {
        $event=$eventrepo->findEventById($id);
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
            return $this->redirectToRoute("valid_resa_media_potins",['id'=>$preO->getId()]);
        }
        $vartwig=$this->menuNav->templatepotins(
            'Wadorder',Links::CUSTOMER_LIST);

        $replace = false;

        return $this->render($this->useragentP.'ptn_media/home.html.twig', [
            'directory'=>'resa',
            'form' => $form->createView(),
            'post'=>$event->getPotin(),
            'date'=>$date,
            'event'=>$event,
            'replacejs'=>$replace,
            'vartwig'=>$vartwig,
            'member'=>$this->member,
            'board'=>$this->board
        ]);
    }

    #[Route('valid-potins-media/{id}', name:"valid_resa_media_potins")]
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
                return $this->redirectToRoute("confirm_resa_media_potins",['id'=>$order->getId()]);
            }else {
                return $this->redirectToRoute("echec_resa_media_potins", ['id' => $order->getId()]);
            }
        }
        $vartwig=$this->menuNav->templatepotins(
            'validpreorderesa',Links::CUSTOMER_LIST);

        $replace = false;

        return $this->render($this->useragentP.'ptn_media/home.html.twig', [
            'directory'=>'resa',
            'form' => $form->createView(),
            'event'=>$event,
            'preorder'=>$preO,
            'registered'=>$nbregistered,
            'replacejs'=>$replace,
            'member'=>$this->member,
            'board'=>$this->board,
            'vartwig'=>$vartwig
        ]);
    }


    #[Route('confirm-potins-media/{id}', name:"confirm_resa_media_potins")] //todo pour confirmation resa
    public function confirmInscriptionPotins($id, Request $request, Commandar $commandar, OrdersRepository $oderrepo): Response
    {
        $order=$oderrepo->findOrderEvent($id);
        $sub=$order->getListproducts()[0]->getSubscription();
        $event=$sub->getEvent();

        $vartwig=$this->menuNav->templatepotins('confirmresa',Links::CUSTOMER_LIST);

        $replace = false;

        return $this->render($this->useragentP.'ptn_media/home.html.twig', [
            'directory'=>'resa',
            'order'=>$order,
            'event'=>$event,
            'sub'=>$sub,
            'replacejs'=>$replace,
            'member'=>$this->member,
            'board'=>$this->board,
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('Echec-resa-potins-media/{id}', name:"echec_resa_media_potins")] //todo pour confirmation resa
    public function echecInscriptionPotins($id, Request $request, Commandar $commandar, OrdersRepository $oderrepo): Response
    {
        $order=$oderrepo->findOrderEvent($id);
        $sub=$order->getListproducts()[0]->getSubscription();
        $event=$sub->getEvent();

        $vartwig=$this->menuNav->templatepotins(
            'confirmresa',Links::CUSTOMER_LIST);

        $replace = false;

        return $this->render($this->useragentP.'ptn_media/home.html.twig', [
            'directory'=>'resa',
            'order'=>$order,
            'event'=>$event,
            'sub'=>$sub,
            'replacejs'=>$replace,
            'member'=>$this->member,
            'board'=>$this->board,
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('edit-info-customer-registered/{idcustomer}', name:"edit_customer_registered_media")]
    public function editCustomerOrderRegisterd(CreatorUser $creatorUser,CustomersRepository $customersRepository,Request $request,$idcustomer): Response
    {
        $customer=$customersRepository->findCustoAndUserById($idcustomer);
        $form = $this->createForm(ProfilresaType::class, $customer->getProfil());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $creatorUser->modifCustomer($customer);
            return $this->redirectToRoute("office_media");
        }
        $vartwig=$this->menuNav->templatepotins('editcustomer',Links::CUSTOMER_LIST);

        $replace = false;

        return $this->render($this->useragentP.'ptn_media/home.html.twig', [
            'directory'=>'resa',
            'form' => $form->createView(),
            'replacejs'=>$replace,
            'member'=>$this->member,
            'board'=>$this->board,
            'vartwig'=>$vartwig
        ]);
    }


    #[Route('edit-participant-potins/{order}/{id}', name:"edit_registered_media")]
    public function editOrderRegisterd(RegisteredRepository $regisrepo,OrdersRepository $ordersRepository,Commandar $commandar,Request $request,$id,$order): Response
    {
        $order=$ordersRepository->findOrderEvent($order);
        $registered=$regisrepo->find($id);
        /*
         $orginalListproduc=new ArrayCollection();
        foreach ($order->getListproducts() as $product){
            if($product->getRegistered()->getId()===$id){
                $orginalListproduc->add($product);
                $registered=$product->getRegistered();
            }
        }
        */
        $form = $this->createForm(RegisteredonlyType::class, $registered);
        $event=$order->getListproducts()[0]->getSubscription()->getEvent();
        //$form = $this->createForm(WorderPotinParticipantsType::class, $order);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
           // dump($form->getData());
           // $commandar->MajResaPotin($form->getData(),$orginalListproduc);
            $commandar->MajPartPotin($registered);
            return $this->redirectToRoute("office_media");
        }
        $vartwig=$this->menuNav->templatepotins('editorderesa',Links::CUSTOMER_LIST);

        $replace = false;

        return $this->render($this->useragentP.'ptn_media/home.html.twig', [
            'directory'=>'resa',
            'form' => $form->createView(),
            'event'=>$event,
            'order'=>$order,
            'registered'=>$registered,
            'replacejs'=>$replace,
            'member'=>$this->member,
            'board'=>$this->board,
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('/form-delete-participant-potins/{order}/{id}', name:"form-delete_participant_resamedia")]
    public function deleteParticipantResa(RegisteredRepository $regisrepo,OrdersRepository $ordersRepository,Commandar $commandar,Request $request,$order,$id): RedirectResponse|Response
    {
        $order=$ordersRepository->findOrderEvent($order);
        $registered=$regisrepo->find($id);
        $event=$order->getListproducts()[0]->getSubscription()->getEvent();

        $form = $this->createForm(DeleteType::class, $registered);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $commandar->deleteOneParticpant($registered);
            return $this->redirectToRoute('office_media');
        }
        $vartwig=$this->menuNav->templatingadmin(
            'deleteorderesa',
            $this->board->getNameBoard(),
            $this->board,
            1
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



    #[Route('/form-delete-resamedia/{orderId}', name:"form-delete_resamedia")]
    public function deleteResa(OrdersRepository $ordersRepository,Commandar $commandar,Request $request,int $orderId): RedirectResponse|Response
    {
        $order=$ordersRepository->findOrderEvent($orderId);
        if (!$order) {
            throw $this->createNotFoundException('Réservation introuvable.');
        }

        $event = null;
        if (!$order->getListproducts()->isEmpty()) {
            $firstProduct = $order->getListproducts()->first();
            $event = $firstProduct?->getSubscription()?->getEvent();
        }
        $form = $this->createForm(DeleteType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $commandar->deleteParticpant($order);
            return $this->redirectToRoute('office_media');
        }

        $vartwig=$this->menuNav->admin(
            $this->board,
            'deleteorderesa',
            links::ADMIN,
            1
        );
        $replace = false;
        return $this->render($this->useragentP.'ptn_media/home.html.twig', [
            'directory'=>'resa',
            'form' => $form->createView(),
            'replacejs'=>$replace,
            'board' => $this->board,
            'event'=>$event,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'vartwig' => $vartwig,
        ]);
    }

}
