<?php

namespace App\Infrastructure\ChabotIA\Typesense;

use App\Infrastructure\ChabotIA\SearchResultItemInterface;
use Exception;

class TypesenseItem implements SearchResultItemInterface
{
    /**
     * An item store by typesense.
     *
     *  {
     *    document: {
     *      field: 'value',
     *      field2: 'value',
     *      field3: 'value',
     *   },
     *   highlights:[
     *      {
     *          field:"title",
     *          snippet: "an excerpt with <mark>",
     *          value: "the whole string with <mark>",
     *      }
     */

    // $data {id: string, title: string, summary : string, content: string, category: string[], info: string, label:string, htlm_titre:string, published_at: int, url:string}

private array $item;

    public function __construct(array $item)
    {
        $this->item = $item;
    }

    public function getId():string
    {
        return $this->item['document']['id'];
    }

    public function getTitle(): string
    {
        /*
        foreach ($this->item['highlights'] as $highlight) {
            if ('title' === $highlight['field']) {
                return $highlight['value'];
            }
        }
        */

        return $this->item['document']['title'];
    }

    public function getSummary():string
    {
        return $this->item['document']['summary'];
    }

    public function getContent():string
    {
        return $this->item['document']['content'];
    }

    public function getCategories(): array
    {
        return $this->item['document']['category'];
    }

    public function getUrl(): string
    {
        return $this->item['document']['url'];
    }

    public function getInfos(): string
    {
        return $this->item['document']['info'];
    }

    public function getLabel(): string
    {
        return $this->item['document']['label'];
    }

    public function getHtmlTitre(): string
    {
        return $this->item['document']['Html_titre'];
    }

    public function getPublishedAt(): string
    {
        return $this->item['document']['published_at'];
    }

    public function getExcerpt(): string
    {
        // Si un extrait est souligné on prend la ligne qui correspond
        foreach ($this->item['highlights'] as $highlight) {
            if ('content' === $highlight['field']) {
                $lines = preg_split("/((\r?\n)|(\r\n?)|(\.\s))/", $highlight['value']);
                if ($lines) {
                    foreach ($lines as $line) {
                        if (false !== strpos($line, '<mark>')) {
                            return $line;
                        }
                    }
                }

                return $highlight['snippet'];
            }
        }

        // Sinon on coupe les X premiers aaractères
        $content = $this->item['document']['content'];
        $characterLimit = 150;
        if (mb_strlen($content) <= $characterLimit) {
            return $content;
        }
        $lastSpace = strpos($content, ' ', $characterLimit);
        if (false === $lastSpace) {
            return $content;
        }

        return substr($content, 0, $lastSpace).'...';
    }
}
