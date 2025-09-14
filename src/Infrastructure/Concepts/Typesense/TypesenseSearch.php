<?php

namespace App\Infrastructure\Concepts\Typesense;

use App\Infrastructure\Concepts\SearchInterface;
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
            "q" => $q,
            "query_by"=> "keyword,concepts,definition",
            "query_by_weights"=> "5,3,1",
            "num_typos"=> 2,
            "split_join_tokens"=> true
        ];

        $resultinterm = $this->client->get('collections/concepts/documents/search?'.Query::build($query));

        ['found' => $found, 'hits' => $items] = $resultinterm;
        return $resultinterm;
    }

    public function searchConcepts(array $keywords): array
    {
        $q = implode(' ', $keywords);
        $query = [
            "q" => $q,
            'query_by' => 'keyword,concepts',
            'query_by_weights' => '5,3',
            'num_typos' => 2,
            'split_join_tokens' => true,
        ];

        try {
            $response = $this->client->get('collections/concepts/documents/search?' . Query::build($query));

            // Si aucun résultat direct, rechercher un fallback générique
            if (empty($response['hits'])) {
                $fallbackquery = [
                    'q' => implode(" ", array_slice($keywords, 0, 1)), // Rechercher un mot-clé principal
                    'query_by' => 'keyword,concepts',
                    'query_by_weights' => '5,3',
                    'num_typos' => 2,
                ];
                $response = $this->client->get('collections/concepts/documents/search?' . Query::build($fallbackquery));
            }

            $concepts = [];
            foreach ($response['hits'] as $hit) {
                $concepts = array_merge($concepts, $hit['document']['concepts'] ?? []);
            }
            return $this->filterRelevantConcepts($concepts, $keywords);

        } catch (\Exception $e) {
        return [];
        }
    }

    public function fetchDefinitions($concept)
    {
        $definition = "";
       // foreach ($concepts as $concept) {

            $query = [
                "q" => $concept,
                "query_by"=> "keyword,concepts,definition",
                "query_by_weights"=> "5,3,1",
                "num_typos"=> 2,
                "split_join_tokens"=> true
            ];
            $response = $this->client->get('collections/concepts/documents/search?'.Query::build($query));
            if (!empty($response['hits'])) {
                $definition = $response['hits'][0]['document']['definition'] ?? 'Définition introuvable.';
            }
       // }

        return $definition;
    }

    private function filterRelevantConcepts(array $concepts, array $keywords): array
    {
        $filteredConcepts = [];

        foreach ($concepts as $concept) {
            // Vérifiez si un mot-clé est contenu dans le concept
            foreach ($keywords as $keyword) {
                if (stripos($concept, $keyword) !== false) {
                    $filteredConcepts[] = $concept;
                    break;
                }
            }
        }

        return array_unique($filteredConcepts); // Évitez les doublons
    }

}
