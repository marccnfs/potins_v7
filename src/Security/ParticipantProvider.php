<?php

// src/Security/ParticipantProvider.php
namespace App\Security;

use App\Entity\Users\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ParticipantProvider
{
    public const SESSION_KEY = 'participant_id';

    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $em
    ) {}

    public function getCurrent(): ?Participant
    {
        $session = $this->requestStack->getSession();
        if (!$session) return null;

        $id = $session->get(self::SESSION_KEY);
        if (!$id) return null;

        /** @var Participant|null $p */
        $p = $this->em->getRepository(Participant::class)->find($id);
        if (!$p) {
            $session->remove(self::SESSION_KEY);
            return null;
        }

        // “rafraîchir” l’id en session (au cas où la session a changé)
        $session->set(self::SESSION_KEY, $p->getId());
        return $p;
    }

    public function setCurrent(?Participant $participant): void
    {
        $session = $this->requestStack->getSession();
        if (!$session) return;

        if ($participant) {
            $session->set(self::SESSION_KEY, $participant->getId());
        } else {
            $session->remove(self::SESSION_KEY);
        }
    }
}

