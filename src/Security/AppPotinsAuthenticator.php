<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppPotinsAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        //$email = $request->getPayload()->getString('email');

        $email = (string) $request->request->get('email', '');
        $password = (string) $request->request->get('password', '');
        $csrf = (string) $request->request->get('_csrf_token', '');

        if ('' === $email) {
            throw new CustomUserMessageAuthenticationException('security.flash.missing_identifier');
        }

        if ('' === $password) {
            throw new CustomUserMessageAuthenticationException('security.flash.missing_password');
        }

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);


        return new Passport(
            new UserBadge($email),
            /*new PasswordCredentials($request->getPayload()->getString('password')),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                new RememberMeBadge(),
            ]
           */
            new PasswordCredentials($password),           // vérification du mot de passe
            [
                new CsrfTokenBadge('authenticate', $csrf),
                new RememberMeBadge(),                    // pour remember_me
            ]


        );



    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        //$userId=$token->getUser();
        $this->removeTargetPath($request->getSession(), $firewallName);

       /* if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }
       */

        return new RedirectResponse($this->urlGenerator->generate('potins_index'));
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $messageKey = match (true) {
            $exception instanceof CustomUserMessageAuthenticationException => $exception->getMessageKey(),
            $exception instanceof BadCredentialsException => 'security.flash.invalid_credentials',
            $exception instanceof InvalidCsrfTokenException => 'security.flash.technical_error',
            default => 'security.flash.technical_error',
        };

        $request->getSession()?->getFlashBag()->add('security', $messageKey);

        return new RedirectResponse($this->urlGenerator->generate(self::LOGIN_ROUTE));
    }
}
