<?php

namespace App\Infrastructure\Search;

interface SearchResultItemInterface
{
    public function getId(): string;

    public function getTitle(): string;

    public function getExcerpt(): string;

    public function getType(): string;

    public function getGps(): string;

    public function getUrl(): string;

    public function getCreatedAt(): \DateTimeInterface;

    public function getCategories(): array;

    public function getPict(): string;

}
