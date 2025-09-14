<?php

namespace App\Infrastructure\ChabotIA;

/**
 * Représente un document indexable par le système de recherche.
 */
class SearchDocument
{
    public string $title;

    public string $content;

    public string $summary;

    public array $category;

    public string $info;

    public string $published_at;
}
