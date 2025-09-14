<?php

namespace App\Infrastructure\Concepts;

/**
 * Représente un document indexable par le système de recherche.
 */
class SearchDocument
{
    public string $keyword;
    public array $concepts;
}
