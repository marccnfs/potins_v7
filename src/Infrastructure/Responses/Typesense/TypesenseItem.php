<?php

namespace App\Infrastructure\Responses\Typesense;

use App\Infrastructure\Responses\SearchResultItemInterface;
use Exception;

class TypesenseItem implements SearchResultItemInterface
{

    private array $item;

    public function __construct(array $item)
    {
        $this->item = $item;
    }
    public function getId():string
    {
        return $this->item['document']['id'];
    }
    public function getKeyword(): string
    {
        return $this->item['document']['keyword'];
    }
    public function getKeywordFind(): string
    {
        return $this->item['document']['keys_find'];
    }
    public function getConcept(): string
    {
        return $this->item['document']['concept'];
    }
    public function getConcepts(): array
    {
        return $this->item['document']['concepts'];
    }
    public function getDefinition(): string
    {
        return $this->item['document']['definition'];
    }
    public function getDescription():string
    {
        return $this->item['document']['description'];
    }
    public function getDate():bool
    {
        return $this->item['document']['date'];
    }
    public function getLink():string
    {
        return $this->item['document']['link'];
    }
    public function getScore(): int
    {
        return $this->item['document']['score'];
    }
    public function getInfo(): string
    {
        return $this->item['document']['info'];
    }
}
