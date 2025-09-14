<?php

namespace App\Controller\Member;

use App\Classe\initMember;
use App\Entity\Customer\Customers;
use App\Entity\Member\Boardslist;
use App\Entity\Users\User;
use App\Form\NewSpwType;
use App\Lib\Links;
use App\Lib\MsgAjax;
use App\Module\Modulator;
use App\Repository\UserRepository;
use App\Service\Member\MemberFactor;
use App\Service\SpaceWeb\BoardlistFactor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[IsGranted('ROLE_MEMBER')]
#[Route('/member/registration/')]


class InitMemberController extends AbstractController
{
    use initMember;


    #[Route('initialisation-de-votre-panneau', name:"intit_board_default")]
    public function newWebsite(MemberFactor $memberFactor,Modulator $modulator, Request $request, EventDispatcherInterface $dispatcher, BoardlistFactor $boardlistor): RedirectResponse|Response
    {
        $boardlist=new Boardslist();
        $form=$this->createForm(NewSpwType::class,$boardlist);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $member=$memberFactor->NewMember($this->customer);
            $board=$boardlistor->createFirstBoard($this->customer,$member,$boardlist, $form);
            $modulator->initModules($this->customer->getServices(), $board);  // creation des modules de base avec le contactation
            $this->em->persist($board);
            $this->em->flush();
          //  $event= new WebsiteCreatedEvent($board);
          //  $dispatcher->dispatch($event, WebsiteCreatedEvent::CREATE);
            return $this->redirectToRoute('office_member');
        }

        $vartwig=$this->menuNav->templatepotins(
            Links::PUBLIC,
            'initboard',
            0,
            "nocity");

        return $this->render($this->useragentP.'ptn_account/home.html.twig', [
            'directory'=>"registration",
            'replacejs'=>$replacejs??null,
            'vartwig'=>$vartwig,
            'website'=>null,
            'customer'=>$this->customer,
            'form'=>$form->createView(),
        ]);
    }

    #[Route('add-new-mediaBoard-ajx', name:"add_new_mediaBoard_ajx")]
    public function addNewMediaBoardAjx(Request $request,MemberFactor $memberFactor,Modulator $modulator, BoardlistFactor $boardlistor): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            $data = json_decode((string) $request->getContent(), true);
            $boardlist=new Boardslist();
            $member=$memberFactor->NewMember($this->customer);
            $board=$boardlistor->createMediaBoard($this->customer,$member,$boardlist,$data);
            $modulator->initModules($this->customer->getServices(), $board);  // creation des modules de base avec le contactation
            return new JsonResponse(true);
        }else{
            return new JsonResponse(MsgAjax::MSG_ERRORRQ);
        }
    }

    //...............en cours :

    #[Route('init-locate-member', name:"init_locate_member")]
    public function initiLocateCustomer(Request $request, UserRepository $userRepository, MemberFactor $dispatchFactor, BoardlistFactor $spaceWebtor): Response
    {
        /** @var User $user */
        $user=$userRepository->findCustomerAndProfilUser($this->security->getUser());
        /** @var Customers $customer */
        $customer=$user->getCustomer();

        $lat=$request->query->get('lat');
        $lon=$request->query->get('lon');

        if($lat && $lon) {
            $coord = ['lat' => $lat, 'lon' => $lon];
            if(!$user->getCharte()) {   //todo faire la validatioçn d'une vrai charte - actuellement mis a true directement a la creation
                $user->setCharte(true);
                $this->em->persist($user);
                $this->em->flush();
            }
            if($dispatch=$customer->getMember()){
                $dispatchFactor->confirmDispatch($dispatch,$coord);
            }else{
                $dispatch=$dispatchFactor->NewMember($customer,$coord);
                $spaceWebtor->createFirstWebsite($customer,$dispatch);
            }

           if (!$this->requestStack->getSession()->has('idcustomer')) $this->sessioninit->initCustomer($user);// todo a savoir pourquoi on test cela ??

            return $this->redirectToRoute('officeboard_member');
            //return $this->redirectToRoute('intit_board_default');
        }else{
            return $this->redirectToRoute('confirmed');  // si pas de loc on retourne a la page de selection de ville
        }
    }

    #[Route('confirm-invit-admin-website/{id}', name:"confirm_invit_admin_website")]
    public function confirInvitAdminWebsite($id, UserRepository $userRepository, BoardlistFactor $spaceWebtor, MemberFactor $dispatchFactor, WebsiteRepository $websiteRepository): Response
    {
        /** @var User $user */
        $user=$userRepository->findCustomerAndProfilUser($this->security->getUser());
        /** @var Customers $customer */
        $customer=$user->getCustomer();
        $website=$websiteRepository->find($id);
        if(!$user->getCharte()) {   //todo faire la validatioçn d'une vrai charte - actuellement mis a true directement a la creation
            $user->setCharte(true);
            $this->em->persist($user);
            $this->em->flush();
        }
        if($dispatch=$customer->getDispatchspace()){
            $spaceWebtor->confirmLocByWebsite($dispatch,$website);
        }else{
            $dispatch=$dispatchFactor->NewDispatchByWebsite($customer,$website);
            $spaceWebtor->createFirstWebsite($customer,$dispatch);
        }
        if (!$this->requestStack->getSession()->has('idcustomer')) $this->sessioninit->initCustomer($user);// todo a savoir pourquoi on test cela ??

        $vartwig = ['maintwig' => "checkInvitadmin", 'title' => "confirmation admin panneau"];
        return $this->render('aff_security/home.html.twig', [
            'directory' => 'registration',
            'replacejs' => $replacejs ?? null,
            'vartwig' => $vartwig,
            'user' => $user,
            'website' => $website,
        ]);

    }

    #[Route('init-member-media', name:"init_member_media")]
    public function initiMember(Request $request, UserRepository $userRepository, MemberFactor $dispatchFactor, BoardlistFactor $spaceWebtor): Response
    {
        /** @var User $user */
        $user=$userRepository->findCustomerAndProfilUser($this->security->getUser());
        /** @var Customers $customer */
        $customer=$user->getCustomer();

        $lat=$request->query->get('lat');
        $lon=$request->query->get('lon');

        if($lat && $lon) {
            $coord = ['lat' => $lat, 'lon' => $lon];
            if(!$user->getCharte()) {   //todo faire la validatioçn d'une vrai charte - actuellement mis a true directement a la creation
                $user->setCharte(true);
                $this->em->persist($user);
                $this->em->flush();
            }
            if($dispatch=$customer->getMember()){
                $dispatchFactor->confirmDispatch($dispatch,$coord);
            }else{
                $dispatch=$dispatchFactor->NewDispatch($customer,$coord);
                $spaceWebtor->createFirstWebsite($customer,$dispatch);
            }

            if (!$this->requestStack->getSession()->has('idcustomer')) $this->sessioninit->initCustomer($user);// todo a savoir pourquoi on test cela ??

            return $this->redirectToRoute('cargo_public');
            //return $this->redirectToRoute('intit_board_default');
        }else{
            return $this->redirectToRoute('confirmed');  // si pas de loc on retourne a la page de selection de ville
        }
    }

}
