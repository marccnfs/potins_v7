<?php

// src/Security/Voter/EscapeGameVoter.php
namespace App\Security\Voter;

use App\Entity\Games\EscapeGame;
use App\Entity\Users\Participant;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EscapeGameVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const VIEW = 'VIEW';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof EscapeGame && \in_array($attribute, [self::EDIT, self::VIEW], true);
    }

    protected function voteOnAttribute(string $attribute, $eg, TokenInterface $token): bool
    {
        // Tu n’utilises pas forcément un User Symfony ici; on se base sur Participant en session.
        $participant = $token->getUser();
        if (!$participant instanceof Participant) {
            // Si tu n’utilises pas le User Token, alors injecte Participant via RequestStack (cf. plus bas)
            return false;
        }

        return match ($attribute) {
            self::VIEW => true, // jeu publié visible; (tu peux restreindre si besoin)
            self::EDIT => $eg->getOwner() && $eg->getOwner()->getId() === $participant->getId(),
            default => false,
        };
    }
}

