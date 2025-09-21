<?php

namespace App\Controller\Security;


use App\Classe\sessionConnect;
use App\Entity\Users\User;
use App\Form\MediathequeType;
use App\Service\Registration\Identificat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]
#[Route('/security/mediatheque')]

class NewMediathequeController extends AbstractController
{
    use sessionConnect;

    #[Route('/registration-mediatheque-stapeone', name:"registration_mediatheque_stpone")]
    public function newMediathequeStapeOne(): Response
    {
        $this->session->remove('identify');
        $vartwig=['maintwig'=>"starting",'title'=>"Inscrivez-vous !"];
        return $this->render('aff_security/home.html.twig', [
            'directory'=>'auto_create_mediatheque',
            'replacejs'=>$replacejs??null,
            'vartwig'=>$vartwig,
        ]);
    }

    //creation d'un acces mediatheque par super_admin
    #[Route('/registration-mediatheque-stapetwo', name:"registration_mediatheque_stptwo")]
    public function newMediathequeStapeTwo(EventDispatcherInterface $eventDispatcher,Identificat $identificator, Request $request): RedirectResponse|Response|null
    {
        $user = New User();
        if(!$this->session->has('identify'))  $this->session->set('identify',uniqid($prefix = "identify", $more_entropy = false));
        $form = $this->createForm(MediathequeType::class, $user);
        $form->get('charte')->setData(true);

        $form->handleRequest($request);
        if($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->session->remove('identify');
                $identificator->creatorMediatheque($user, $form);
                $user->setConfirmationToken(null);
                $user->setEnabled(true);
                // $user->setCharte(true);
                $user->setActive(true);
                $this->em->persist($user);
                $this->em->flush();
                return $this->redirectToRoute('intit_espace_media',['id'=>$user->getId()]);
            }
        }

        $vartwig=['maintwig'=>"identifyOneMediatheque",'title'=>""];
        return $this->render('aff_security/home.html.twig', [
            'directory'=>'auto_create_mediatheque',
            'replacejs'=>$replacejs??null,
            'vartwig'=>$vartwig,
            'identify'=>$this->session->get('identify'),
            'choice'=>"media",
            'form' => $form->createView()]);
    }

    #[Route('/registration-mediatheque-end', name:"registration_mediatheque_end")]
    public function endRegistrationMediatheque(): Response
    {
        $vartwig=['maintwig'=>"end",'title'=>"inscription réussie"];
        return $this->render('aff_security/home.html.twig', [
            'directory'=>'auto_create_mediatheque',
            'replacejs'=>$replacejs??null,
            'vartwig'=>$vartwig,
        ]);
    }
}
