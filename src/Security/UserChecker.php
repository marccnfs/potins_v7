<?php


namespace App\Security;

use App\Entity\Users\User as AppUser;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        if (!$user->isAccountNonLocked()) {
            $ex = new LockedException('User account is locked.');
            $ex->setUser($user);
            throw $ex;
        }

        if (!$user->isEnabled()) {
            $ex = new DisabledException('User account is disabled.');
            $ex->setUser($user);
            throw $ex;
        }

        if (!$user->isAccountNonExpired()) {
                $ex = new AccountExpiredException('User account has expired.');
                $ex->setUser($user);
                throw $ex;
        }

    }

    public function checkPostAuth(UserInterface $user, $token = null): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        // user account is expired, the user may be notified
        if (!$user->isAccountNonExpired()) {
            throw new AccountExpiredException('...');
        }

        if (!$user->isCredentialsNonExpired()) {
            $ex = new CredentialsExpiredException('User credentials have expired.');
            $ex->setUser($user);
            throw $ex;
        }
    }


}
