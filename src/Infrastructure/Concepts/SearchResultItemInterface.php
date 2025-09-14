<?php

namespace App\Infrastructure\Concepts;

interface SearchResultItemInterface
{
    public function getId(): string;

    public function getKeyword(): string;

    public function getConcepts(): array;

    public function getDefinition(): string;
}
