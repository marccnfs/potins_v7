<?php

namespace App\Controller\Security;

use App\Entity\Users\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;


class SecurityController extends AbstractController
{

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $helper,RequestStack $requestStack): Response
    {

        /* old argument => #[CurrentUser] ?User $user, Request $request,*/

        // get the login error if there is one
        if (preg_match('/mob/i', $_SERVER['HTTP_USER_AGENT'])) {
            $requestStack->getSession()->set('agent','mobile/');
        } else {
            $requestStack->getSession()->set('agent','desk/');
        }

        $error = $helper->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $helper->getLastUsername();

        $vartwig=['maintwig'=>"login",'title'=>"connexion"];
        return $this->render('aff_security/home.html.twig', [
            'directory'=>'log',
            'vartwig'=>$vartwig,
            'replacejs'=>null,
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(RequestStack $requestStack)
    {
        $requestStack->getSession()->invalidate();
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }

}


