<?php


namespace App\Util;


use App\Entity\Users\User;

class Canonicalizer
{
    public function canonicalize($string)
    {
        if (null === $string) {
            return;
        }

        $encoding = mb_detect_encoding($string);
        $result = $encoding
            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
            : mb_convert_case($string, MB_CASE_LOWER);

        return $result;
    }

    public function updateCanonicalFields(User $user)
    {
        $user->setEmailCanonical($this->canonicalize($user->getEmail()));
    }


    public function canonicalizeEmail(User $user)
    {

        $user->setEmailCanonical($this->canonicalize($user->getEmail()));
    }

}