<?php

namespace App\Service;


use App\Entity\Users\Participant;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class ParticipantContext
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public function getParticipantOrFail(): Participant
    {
        $session = $this->requestStack->getSession();
        $participant = $session->get('_participant');
        if (!$participant instanceof Participant) {
            throw new AccessDeniedHttpException('Participant requis.');
        }
        return $participant;
    }

}
