<?php

namespace App\Infrastructure\ChabotIA\Typesense;

use App\Infrastructure\ChabotIA\SearchInterface;
use App\Infrastructure\ChabotIA\SearchResult;
use GuzzleHttp\Psr7\Query;

class TypesenseSearch implements SearchInterface
{
    private TypesenseClient $client;

    public function __construct(TypesenseClient $client)
    {
        $this->client = $client;
    }

    public function search(string $q): array//SearchResult
    {
        $query = [
            'q' => $q,
            'query_by' => 'title,summary,content',
            //'sort_by' => 'published_at:desc',
            'per_page' => 3,
        ];

        $resultinterm = $this->client->get('collections/ressources/documents/search?'.Query::build($query));

        ['found' => $found, 'hits' => $items] = $resultinterm;

        $limit = 5;
        //$returninterm= new SearchResult(array_map(fn (array $item) => new TypesenseItem($item), $items), min($found, 10 * $limit));
         //   dump($returninterm);

        return $resultinterm;
    }
}
