<?php

namespace App\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, RequestStack $requestStack): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }
        if (preg_match('/mob/i', $_SERVER['HTTP_USER_AGENT'])) {
            $requestStack->getSession()->set('agent','mobile/');
        } else {
            $requestStack->getSession()->set('agent','desk/');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $vartwig=['maintwig'=>"login",'title'=>"connexion"];
        return $this->render('aff_security/home.html.twig', [
            'directory'=>'log',
            'vartwig'=>$vartwig,
            'replacejs'=>null,
            'last_username' => $lastUsername,
            'error' => $error
        ]);

    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
