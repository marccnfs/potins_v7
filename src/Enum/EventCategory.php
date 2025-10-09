<?php

namespace App\Enum;

enum EventCategory: string
{
    case ATELIER = 'atelier';
    case RDV = 'rdv';
    case PERMANENCE = 'permanence';
    case EXTERNE = 'externe';
    case FORMATION = 'formation';
    case INDISPONIBLE = 'indispo';
    case AUTRE = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::ATELIER      => 'Atelier collectif',
            self::RDV          => 'RDV public',
            self::PERMANENCE   => 'Permanence',
            self::EXTERNE      => 'Événement externe',
            self::FORMATION    => 'Formation',
            self::INDISPONIBLE => 'Indisponible',
            self::AUTRE        => 'Autre activité',
        };
    }

    public function isBookable(): bool
    {
        return match ($this) {
            self::ATELIER, self::RDV, self::PERMANENCE => true,
            default => false,
        };
    }
}
