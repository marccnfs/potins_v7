<?php

namespace App\Infrastructure\Responses;

interface SearchInterface
{
    public function search(string $q);

    public function fetchDefinitions(string $concept);

    public function searchConceptResponses(string $stringKeywords, array $arrayKeyword);
}
