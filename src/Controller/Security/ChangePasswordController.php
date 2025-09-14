<?php


namespace App\Controller\Security;

use App\Email\RegistrationMailer;
use App\AffiEvents;
use App\Event\FormEvent;
use App\Event\FilterUserResponseEvent;
use App\Event\GetResponseNullableUserEvent;
use App\Event\GetResponseUserEvent;
use App\Form\ChangePasswordType;
use App\Repository\UserRepository;
use App\Util\TokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



#[Route('security/oderder/profil-password/')]

class ChangePasswordController extends AbstractController
{
    private EventDispatcherInterface $eventDispatcher;
    private TokenGeneratorInterface $tokenGenerator;
    private RegistrationMailer $mailer;
    private int $retryTtl;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em ,EventDispatcherInterface $eventDispatcher, TokenGeneratorInterface $tokenGenerator, RegistrationMailer$mailer)
    {
        $this->em=$em;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
        $this->retryTtl = 60; //3600;
    }


    #[Route('forget-mot-de-passe', name:"forget_password_request")]
    public function forgetPassword(RequestStack $requestStack): Response
    {
        $session=$requestStack->getSession();
        if (preg_match('/mob/i', $_SERVER['HTTP_USER_AGENT'])) {
            $session->set('agent','mobile/');
        } else {
            $session->set('agent','desk/');
        }
        $vartwig=['maintwig'=>"request",'title'=>"renouvellez votre mot de passe"];
        return $this->render('aff_security/home.html.twig', [
            'directory'=>'change_password',
            'vartwig'=>$vartwig,
            'replacejs'=>null,
            'titleidf'=>"mot de passe oublié ?"
        ]);
    }


    #[Route('check-new-password-by-mail', name:"passeword_check_email")]
    public function checkEmail(Request $request, UserRepository $userRepository,RequestStack $requestStack): RedirectResponse|Response
    {
        $session=$requestStack->getSession();
        if (preg_match('/mob/i', $_SERVER['HTTP_USER_AGENT'])) {
            $session->set('agent','mobile/');
        } else {
            $session->set('agent','desk/');
        }
        $username = $request->query->get('username');
        if (empty($username)) {
            return new RedirectResponse($this->generateUrl('forget_password_request'));
        }
        $user = $userRepository->findUserByEmail($username);

        $vartwig=['maintwig'=>"check_email",'title'=>"renouvellez votre mot de passe"];
        return $this->render('aff_security/home.html.twig', [
            'directory'=>'change_password',
            'vartwig'=>$vartwig,
            'replacejs'=>null,
            'tokenLifetime' => ceil($this->retryTtl / 3600),
            'user'=>$user,
            'titleidf'=>"Votre identifiant de connexion"
        ]);
    }


    #[Route('reset-change-password', name:"reset_change_password")]
    public function changePasswordofForget(Request $request,Response $response, UserRepository $userRepository,RequestStack $requestStack): RedirectResponse|Response
    {
        $session=$requestStack->getSession();
        if (preg_match('/mob/i', $_SERVER['HTTP_USER_AGENT'])) {
            $session->set('agent','mobile/');
        } else {
            $session->set('agent','desk/');
        }
        $token = $request->query->get('token');
        $user = $userRepository->findUserByConfirmationToken($token);
        if ( null === $user) {
            return  new  RedirectResponse ($this->generateUrl( 'app_login' ));
        }

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch($event, Affievents::RESETTING_RESET_INITIALIZE);
        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }
        $form =$this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch($event, Affievents::RESETTING_RESET_SUCCESS);
            $this->updateUser($user);
            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }
            $this->eventDispatcher->dispatch(new FilterUserResponseEvent($user, $request, $response), Affievents::RESETTING_RESET_COMPLETED);
            return $response;
        }

        $vartwig=['maintwig'=>"change_password",'title'=>"renouvellez votre mot de passe"];
        return $this->render('aff_security/home.html.twig', [
            'directory'=>'change_password',
            'vartwig'=>$vartwig,
            'replacejs'=>null,
            'token'=>$token,
            'form' => $form->createView(),
            'titleidf'=>"Votre identifiant de connexion"
        ]);

    }

    protected function updateUser($user){
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }


    /*-----------------------------------------------------   old a verfier -----------------------------------------------------------------*/


    #[Route('email-inconnu', name:"fno_email_find")]
    public function noEmail(): Response
    {
        return $this->render('security/ChangePassword/nofind.html.twig');
    }

    /**
     * recoit l'adresse mail, verifie et envoie le mail de confirm
     */
    #[Route('control-forget-mot-de-passe-to-mail', name:"forgetpassword_send_email")]
    public function sendEmail(Request $request, UserRepository $userRepository,EntityManagerInterface $em)
    {
        $email = $request->request->get('email');
        $user = $userRepository->findUserByEmail($email);
        $event = new GetResponseNullableUserEvent($user, $request);
        $this->eventDispatcher->dispatch($event, Affievents::CHANGE_PASSWORD_TEST);



        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        if (null !== $user && !$user->isPasswordRequestNonExpired($this->retryTtl)) {
            $event = new GetResponseUserEvent($user, $request);
            $this->eventDispatcher->dispatch($event,Affievents::RESETTING_RESET_REQUEST);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            $event = new GetResponseUserEvent($user, $request);
            $this->eventDispatcher->dispatch($event,Affievents::RESETTING_SEND_EMAIL_CONFIRM);
            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            $this->mailer->sendNewPasswordEmailMessage($user);
            $user->setPasswordRequestedAt(new \DateTime());
            $em->persist($user);
            $em->flush();
            $event = new GetResponseUserEvent($user, $request);
            $this->eventDispatcher->dispatch($event,Affievents::RESETTING_SEND_EMAIL_COMPLETED);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }
        }
        return new RedirectResponse($this->generateUrl('passeword_check_email', array(
            'user'=>$user,
            'username' => $email,
            'titleidf'=>"Votre identifiant de connexion"
        )));
    }






}
