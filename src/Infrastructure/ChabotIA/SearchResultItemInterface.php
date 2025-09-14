<?php

namespace App\Infrastructure\ChabotIA;

interface SearchResultItemInterface
{
    public function getId(): string;

    public function getTitle(): string;

    public function getExcerpt(): string;

    public function getSummary(): string;

    public function getInfos(): string;

    public function getUrl(): string;

    public function getLabel(): string;

    public function getPublishedAt(): string;

    public function getCategories(): array;

    public function getHtmlTitre(): string;

}
