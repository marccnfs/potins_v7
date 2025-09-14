<?php

// src/Http/Resolver/CurrentParticipantResolver.php
namespace App\Http\Resolver;

use App\Entity\Users\Participant;
use App\Security\ParticipantProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class CurrentParticipantResolver implements ValueResolverInterface
{
    public function __construct(private ParticipantProvider $provider){}
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if ($argument->getType() !== Participant::class) {
            return [];
        }

        // On injecte ce que le Subscriber a posé ; sinon null
        $p = $request->attributes->get('_participant');
        if ($p instanceof Participant) {
            return [$p];
        }

        $p = $this->provider->getCurrent();
        if($p instanceof Participant) {
            $request->attributes->set('_participant', $p);
            return [$p];
        }

        // Autorise aussi l’injection nullable si l’action n’est PAS marquée RequireParticipant
        if ($argument->isNullable()) {
            return [null];
        }

        // Si non-nullable et pas de participant (hors routes protégées), on laisse Symfony gérer (erreur typehint)
        return [];
    }
}

