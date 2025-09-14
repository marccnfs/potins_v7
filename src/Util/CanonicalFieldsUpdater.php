<?php


namespace App\Util;


use App\Entity\Users\User;


class CanonicalFieldsUpdater
{
    private $emailCanonicalizer;

    public function __construct(Canonicalizer $emailCanonicalizer)
    {
        $this->emailCanonicalizer = $emailCanonicalizer;
    }

    public function updateCanonicalFields(User $user)
    {
        $user->setEmailCanonical($this->canonicalizeEmail($user->getEmail()));
    }

    /**
     * Canonicalizes an email.
     *
     * @param string|null $email
     *
     * @return string|null
     */
    public function canonicalizeEmail($email)
    {
        return $this->emailCanonicalizer->canonicalize($email);
    }

}