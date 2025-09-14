<?php

namespace App\Infrastructure\Concepts;

interface SearchInterface
{
    public function search(string $q);

    public function searchConcepts(array $keywords);

    public function fetchDefinitions(string $concept);
}
