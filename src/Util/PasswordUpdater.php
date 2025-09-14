<?php


namespace App\Util;


use App\Entity\Users\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordUpdater
{
    private UserPasswordHasherInterface $passwordEncoder;

    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function hashPassword(User $user, $form)
    {
        $plainPassword = $user->getPlainPassword();
        $user->setPassword($this->passwordEncoder->hashPassword($user, $form->get('plainPassword')->getData()));
        $user->eraseCredentials();
    }
    public function hashPasswordstring(User $user, $mdp)
    {
        $plainPassword = $user->getPlainPassword();
        $user->setPassword($this->passwordEncoder->hashPassword($user, $mdp));
        $user->eraseCredentials();
    }
}