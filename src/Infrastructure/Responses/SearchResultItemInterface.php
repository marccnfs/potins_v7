<?php

namespace App\Infrastructure\Responses;

interface SearchResultItemInterface
{
    public function getId(): string;

    public function getKeyword(): string;

    public function getKeywordFind(): string;

    public function getConcept(): string;

    public function getConcepts(): array;

    public function getDefinition(): string;

    public function getDescription(): string;

    public function getDate(): bool;

    public function getLink(): string;

    public function getScore(): int;

    public function getInfo(): string;

}
