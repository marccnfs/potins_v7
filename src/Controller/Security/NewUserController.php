<?php

namespace App\Controller\Security;


use App\AffiEvents;
use App\Classe\sessionConnect;
use App\Entity\Users\User;
use App\Event\FormEvent;
use App\Form\UserType;
use App\Service\Registration\Identificat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/security/admin')]

class NewUserController extends AbstractController
{
    use sessionConnect;

    #[Route('/new-Identify', name:"new_identify")]
    public function controlNewIdentify(): RedirectResponse|Response|null
    {
        if ($this->session->has('identify')) $this->session->remove('identify');
        return $this->redirectToRoute('choice_registration');
    }

    #[Route('/new-Identify-for-resa', name:"new_identify_for_resa")]
    public function controlNewIdentifyForResa(): RedirectResponse|Response|null
    {
        if ($this->session->has('identify')) $this->session->remove('identify');
        return $this->redirectToRoute('registration_resa_public');
    }

    #[Route('/choice-registration', name:"choice_registration")]
    public function choiceRegistration(): Response
    {
        $vartwig=['maintwig'=>"choiceregistration",'title'=>"Inscrivez-vous !"];
        return $this->render('aff_security/home.html.twig', [
            'directory'=>'auto_create',
            'replacejs'=>$replacejs??null,
            'vartwig'=>$vartwig,
            'identify'=>$this->session->get('identify')
            ]);
    }


    #[Route('/registration-resa-public', name:"registration_resa_public")]
    public function registrationResaPublic(): Response
    {
        $vartwig=['maintwig'=>"registrationforresa",'title'=>"Inscrivez-vous !"];
        return $this->render('aff_security/home.html.twig', [
            'directory'=>'auto_create',
            'replacejs'=>$replacejs??null,
            'vartwig'=>$vartwig,
            'identify'=>$this->session->get('identify')
        ]);
    }

    #[Route('/new-Identify-pro', name:"inscrip_pro")]
    public function controlNewIdentifyPro(): RedirectResponse|Response|null
    {
        if ($this->session->has('identifypro')) $this->session->remove('identifypro');
        return $this->redirectToRoute('choice_registration_pro');
    }


    #[Route('/create-board-stape-usager', name:"new_identify_stape_usager")]
    public function newIdentifyUsager(EventDispatcherInterface $eventDispatcher,Identificat $identificator, Request $request): RedirectResponse|Response|null
    {
        $user = New User();
        if(!$this->session->has('identify'))  $this->session->set('identify',uniqid($prefix = "identify", $more_entropy = false));
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->session->remove('identify');
                $identificator->creatorusager($user, $form);
                $event = new FormEvent($form, $request);
                $eventDispatcher->dispatch($event, AffiEvents::REGISTRATION_SUCCESS);
                $this->em->persist($user);
                $this->em->flush();

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('confirmed');
                    $response = new RedirectResponse($url);
                }
                /*
                $eventDispatcher->dispatch( new FilterUserResponseEvent($user, $request, $response),AffiEvents::REGISTRATION_COMPLETED);
                */
                return $response;
            }
            $event = new FormEvent($form, $request);
            $eventDispatcher->dispatch($event, AffiEvents::REGISTRATION_FAILURE );
            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }
        $vartwig=['maintwig'=>"identifyOne",'title'=>""];
        return $this->render('aff_security/home.html.twig', [
            'directory'=>'auto_create',
            'replacejs'=>$replacejs??null,
            'vartwig'=>$vartwig,
            'identify'=>$this->session->get('identify'),
            'form' => $form->createView()]);
    }

    #[Route('/create-board-stape-mediator', name:"new_identify_stape_mediator")]
    public function newIdentifyMediator(EventDispatcherInterface $eventDispatcher,Identificat $identificator, Request $request): RedirectResponse|Response|null
    {
        $user = New User();
        if(!$this->session->has('identify'))  $this->session->set('identify',uniqid($prefix = "identify", $more_entropy = false));
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->session->remove('identify');
                $identificator->creatormember($user, $form);
                $event = new FormEvent($form, $request);
                $eventDispatcher->dispatch($event, AffiEvents::REGISTRATION_SUCCESS);
                $this->em->persist($user);
                $this->em->flush();

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('confirmed');
                    $response = new RedirectResponse($url);
                }
                /*
                $eventDispatcher->dispatch( new FilterUserResponseEvent($user, $request, $response),AffiEvents::REGISTRATION_COMPLETED);
                */
                return $response;
            }
            $event = new FormEvent($form, $request);
            $eventDispatcher->dispatch($event, AffiEvents::REGISTRATION_FAILURE );
            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }
        $vartwig=['maintwig'=>"identifyOne",'title'=>""];
        return $this->render('aff_security/home.html.twig', [
            'directory'=>'auto_create',
            'replacejs'=>$replacejs??null,
            'vartwig'=>$vartwig,
            'identify'=>$this->session->get('identify'),
            'form' => $form->createView()]);
    }

}
