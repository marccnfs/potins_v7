<?php

namespace App\Controller\Customer;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Classe\UserSessionTrait;
use App\Entity\Customer\Customers;
use App\Lib\Links;
use App\Repository\PrivateConversRepository;
use App\Service\Messages\PrivateMessageor;
use App\Service\Registration\Sessioninit;
use App\Service\SpaceWeb\BoardlistFactor;
use App\Form\ProfilType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_USER')]
#[Route('/customer/profil/')]

class ProfilCustomerController extends AbstractController
{
    use UserSessionTrait;


    #[Route('mon-espace-affichange', name:"profil_dispatch")]
    public function profilDispatch(PaginatorInterface $paginator, PrivateConversRepository $privateConversRepository, PrivateMessageor $messageor, Request $request): RedirectResponse|Response
    {

        $vartwig=$this->menuNav->templatePotins(
            'profilshow',
            Links::CUSTOMER_LIST,
        );

        return $this->render($this->useragentP.'ptn_customer/home.html.twig', [
            'directory'=>"profil",
           'replacejs'=>$replacejs??null,
            'board'=>$this->board,
            //"route"=>"locate",
            'tfile'=>"namedispatch",
            'dispatch'=>$this->member,
            'vartwig'=>$vartwig,
            'permissions'=>[0,0,0],
            'locatecity'=>0,
        ]);
    }


    #[Route('mon-compte-infos', name:"profil_customer")]
    public function profilCustomer(): RedirectResponse|Response
    {

        $vartwig=$this->menuNav->templatepotins('profilcontact',Links::CUSTOMER_LIST);

        return $this->render($this->useragentP.'ptn_customer/home.html.twig', [
            'directory'=>"profil",
            'replacejs'=>$replacejs??null,
            'customer'=>$this->currentCustomer,
            'vartwig'=>$vartwig,
            'permissions'=>[0,0,0],
        ]);
    }


    #[Route('modification-nom-compte', name:"edit_profil_customer")]
    public function updateProfilCustomer(Request $request, Sessioninit $sessioninit): RedirectResponse|Response
    {
        $customer=$this->customer;
        $form=$this->createForm(ProfilType::class,$this->member->getCustomer()->getProfil());
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->em->flush();
            //$this->reClearInit();
            //$sessioninit->initSession($this->member);
          //  $this->addFlash('success', 'Vos informations ont bien été mises à jour');
            return $this->redirectToRoute('profil_customer');
        }
        $vartwig=$this->menuNav->templatePotins(
            'profiledit',Links::CUSTOMER_LIST,
          );

        return $this->render($this->useragentP.'ptn_customer/home.html.twig', [
            'replacejs'=>$replacejs??null,
            'directory'=>"profil",
            'customer'=>$this->currentCustomer,
            'profil'=>$customer->getProfil(),
            'vartwig'=>$vartwig,
            'form'=>$form->createView(),
            'permissions'=>[0,0,0],
        ]);
    }


    #[Route('infos-contacts', name:"update_infocontact")]
    public function updateSpace(Request $request, BoardlistFactor $spaceWebtor, Sessioninit $sessioninit): RedirectResponse|Response
    {
        if(!$this->member) return $this->redirectToRoute('cargo_public');
        $this->activeBoard();
        $customer=$this->member->getCustomer();
        /** @var Customers $customer */
        $identity=$customer->getProfil();
        if($this->member->getLocality()==null)return $this->redirectToRoute('spaceweblocalize_init');
        $form=$this->createForm(ProfilType::class,$identity);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            // $spaceWebtor->majDistach( $member, $form); //todo ???? a voir
            $this->em->flush();
            $this->reClearInit();
            $sessioninit->initSession($this->currentMember);
            $this->addFlash('success', 'Vos informations ont bien été mises à jour');
            return $this->redirectToRoute('profil_customer');
        }
        $vartwig=$this->menuNav->templatePotins(
            'profiledit',Links::CUSTOMER_LIST,
             );

        return $this->render($this->useragentP.'ptn_customer/home.html.twig', [
            'directory'=>"profil",
            'replacejs'=>$replacejs??null,
            'twigform'=>'form_update',
            'customer'=>$this->currentCustomer,
            'member'=>$this->currentMember,
            'board'=>$this->board,
            'vartwig'=>$vartwig,
            'form'=>$form->createView(),
            'permissions'=>$this->permission,
            'locatecity'=>0,
        ]);
    }


}
