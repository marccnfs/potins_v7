<?php

namespace App\Infrastructure\Concepts\Typesense;

use App\Infrastructure\Concepts\SearchResultItemInterface;
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

    public function getConcepts(): array
    {
        return $this->item['document']['concepts'];
    }

    public function getDefinition():string
    {
        return $this->item['document']['definition'];
    }
}
