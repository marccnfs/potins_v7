<?php
namespace App\Enum;

enum EventStatus: string
{
    case SCHEDULED = 'scheduled';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case DRAFT = 'draft';
}
