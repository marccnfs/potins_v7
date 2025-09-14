<?php


namespace App\Controller\Security;


use App\Entity\Users\User;
use App\AffiEvents;
use App\Repository\TabDotWbRepository;
use App\Repository\UserRepository;
use App\Event\GetResponseUserEvent;
use App\Service\Registration\Sessioninit;
use App\Util\CanonicalFieldsUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


#[Route('security/oderder/check')]

class CheckRegistrationController extends AbstractController
{
    private EventDispatcherInterface $eventDispatcher;
    private TokenStorageInterface $tokenStorage;
    private CanonicalFieldsUpdater $canonicalFieldsUpdater;
    private Sessioninit $sessioninit;

    public function __construct(EventDispatcherInterface $eventDispatcher, TokenStorageInterface $tokenStorage, CanonicalFieldsUpdater $canonicalFieldsUpdater,Sessioninit $sessioninit)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->canonicalFieldsUpdater = $canonicalFieldsUpdater;
        $this->sessioninit = $sessioninit;
    }

//ici la page qi affiche que le compte est crée et que l'uasger doit aller sur sa messagerie pour confirme r son mail
    #[Route('/check-mail', name:"registration_check_email")]
    public function checkEmail(RequestStack $requestStack, UserRepository $userRepository): RedirectResponse|Response
    {
        $session=$requestStack->getSession();
        $email = $requestStack->getSession()->get('send_confirmation_email/email');
        if (empty($email)) {
            return new RedirectResponse($this->generateUrl('new_identify'));
        }
        $session->remove('send_confirmation_email/email');
        $session->remove('idcustomer');
        $session->remove('typeuser');

        //todo verfi si ces controles sont vraiment necessaires???
        $emailcanonical= $this->canonicalFieldsUpdater->canonicalizeEmail($email);
        $user = $userRepository->findUserByEmail($emailcanonical);
        if (null === $user) {
            return $this->redirectToRoute('app_login');
        }

        $vartwig=['maintwig'=>"check_email",'title'=>"Votre espace AffiChanGe est confirmé"];
        return $this->render('aff_security/home.html.twig', [
            'directory'=>'registration',
            'replacejs'=>$replacejs??null,
            'vartwig'=>$vartwig,
            'user' => $user,
        ]);
    }


    #[Route('/no-check-mail/{user}', name:"no-registration_check_email")]
    public function nocheckEmail($user): RedirectResponse|Response
    {
        if (null === $user) {
            return $this->redirectToRoute('app_login');
        }

        $vartwig=['maintwig'=>"no_check_email",'title'=>"no check mail"];
        return $this->render('aff_security/home.html.twig', [
            'directory'=>'registration',
            'replacejs'=>$replacejs??null,
            'vartwig'=>$vartwig,
            'user' => $user,
        ]);
    }


    //ici le retour du lien du mail de confirmation de creation de compte et demande de validation de l'adresse mail
    #[Route('/Confirme-inscription/{token}', name:"registration_confirm")]
    public function confirmByTokenMail($token, UserRepository $reposiUser,EntityManagerInterface $em): RedirectResponse|Response|null
    {
        $user = $reposiUser->findUserByConfirmationToken($token);
        if (null !== $user) {
            $user->setConfirmationToken(null);
            $user->setEnabled(true);
            $em->persist($user);
            $em->flush();
        }
        return $this->redirectToRoute('app_login');

        // todo en cas d'erreur de token faire uun message vers l'utilisateur
        //$event = new GetResponseUserEvent($user, $request);
       // $this->eventDispatcher->dispatch($event, AffiEvents::REGISTRATION_CONFIRM );

        /*
        if (null === $response = $event->getResponse()) {
            $this->sessioninit->initCustomer($user);
            $url = $this->generateUrl('confirmed');
            $response = new RedirectResponse($url);
        }
        //en stand by pour l'instant
        // $this->eventDispatcher->dispatch(Affievents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));
        return $response;
        */
    }

    #[Route('/Confirme-invitation/{token}', name:"dispatch_confirm")]
    public function confirmInviteDispatchTokenMail(Request $request, $token, UserRepository $reposiUser,EntityManagerInterface $em): RedirectResponse
    {
        $user = $reposiUser->findUserByConfirmationToken($token);
        if (null === $user) {
            return $this->redirectToRoute('app_login');  // todo faire une redirction pour informer de l'echec
       }
        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch($event, AffiEvents::REGISTRATION_CONFIRM );
        $em->persist($user);
        $em->flush();

        if (null === $response = $event->getResponse()) {
            $this->sessioninit->initCustomer($user);
            $url = $this->generateUrl('confirmed');
            $response = new RedirectResponse($url);
        }

        //en stand by pour l'instant
        // $this->eventDispatcher->dispatch(Affievents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));

        return $response;
    }


    #[Route('/registration-confirmee', name:"confirmed")]
    public function confirmedInscription(TabDotWbRepository $tabDotWbRepository): Response
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $tabdot = $tabDotWbRepository->findOneBy(['email' => $user->getEmailCanonical()]);
        if ($tabdot) return $this->redirectToRoute('confirm_invit_admin_website',['id'=>$tabdot->getBoard()->getId()]);

        $vartwig = ['maintwig' => "confirmed", 'title' => "Bravo, votre espace AffiChanGe est ouvert"];
        return $this->render('aff_security/home.html.twig', [
            'directory' => 'registration',
            'replacejs' => $replacejs ?? null,
            'vartwig' => $vartwig,
            'user' => $user,
            'tag' => ['name' => $city ?? null, 'active' => true, 'l_class' => "init"],
        ]);
    }


    private function getTargetUrlFromSession(RequestStack $requestStack): ?string
    {
       // dump($this->tokenStorage->getToken(),$session);
        $session = $requestStack->getSession();
        $key = sprintf('_security.%s.target_path', $this->tokenStorage->getToken()->getProviderKey());

        if ($session->has($key)) {
            return $session->get($key);
        }
        return null;
    }
}