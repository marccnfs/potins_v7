<?php

namespace App\Enum;

enum EventVisibility: string
{
    case PUBLIC = 'public';
    case UNLISTED = 'unlisted';
    case PRIVATE = 'private';
}
