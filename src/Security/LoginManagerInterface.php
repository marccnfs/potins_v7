<?php


namespace App\Security;


use App\Entity\Users\User;
use Symfony\Component\HttpFoundation\Response;

interface LoginManagerInterface
{
    public function logInUser($firewallName, User $user, Response $response = null);
}