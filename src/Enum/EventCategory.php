<?php

namespace App\Enum;

enum EventCategory: string
{
    case ATELIER = 'atelier';
    case RDV = 'rdv';
    case EXTERNE = 'externe';
    case AUTRE = 'autre';
}
