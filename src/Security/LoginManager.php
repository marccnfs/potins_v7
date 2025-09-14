<?php


namespace App\Security;


use App\Entity\Users\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

class LoginManager implements LoginManagerInterface
{

    private TokenStorageInterface $tokenStorage;
    private UserCheckerInterface $userChecker;
    private SessionAuthenticationStrategyInterface $sessionStrategy;
    private RequestStack $requestStack;


    /**
     * LoginManager constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param UserCheckerInterface $userChecker
     * @param SessionAuthenticationStrategyInterface $sessionStrategy
     * @param RequestStack $requestStack
     */
    public function __construct(TokenStorageInterface $tokenStorage, UserCheckerInterface $userChecker,
                                SessionAuthenticationStrategyInterface $sessionStrategy,
                                RequestStack $requestStack

    ) {
        $this->tokenStorage = $tokenStorage;
        $this->userChecker = $userChecker;
        $this->sessionStrategy = $sessionStrategy;
        $this->requestStack = $requestStack;
    }


    final public function logInUser($firewallName, User $user, Response $response = null)
    {
        $this->userChecker->checkPreAuth($user);

        $token = $this->createToken($firewallName, $user);
        $request = $this->requestStack->getCurrentRequest();

        if (null !== $request) {
            $this->sessionStrategy->onAuthentication($request, $token);

            if (null !== $response && null !== $this->rememberMeService) {
                $this->rememberMeService->loginSuccess($request, $response, $token);
            }
        }
        $this->tokenStorage->setToken($token);
    }


    protected function createToken(string $firewall, User $user): UsernamePasswordToken
    {
        return new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
    }
}