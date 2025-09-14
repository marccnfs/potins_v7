<?php

namespace App\Infrastructure\Responses;

/**
 * Représente un document indexable par le système de recherche.
 */
class SearchDocument
{
    public string $keys_find;
    public string $concept;
}
