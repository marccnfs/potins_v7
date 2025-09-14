<?php

namespace App\Infrastructure\Responses\Typesense;

use App\Infrastructure\Responses\SearchInterface;
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
            "query_by"=> "keys_find,concept,description",
            "query_by_weights"=> "5,3,2",
            "num_typos"=> 2
        ];

        $resultinterm = $this->client->get('collections/responses/documents/search?'.Query::build($query));

        ['found' => $found, 'hits' => $items] = $resultinterm;
        return $resultinterm;
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

    public function searchConceptResponses($stringKeywords,$arrayKeyword): array
    {

        $q = $stringKeywords;
        $query = [
            "q" => $q,
            'query_by' => 'concept',
            'num_typos' => 2,
        ];

        try {
            $response = $this->client->get('collections/responses/documents/search?' . Query::build($query));

            // Si aucun résultat direct, rechercher un fallback générique
            if (empty($response['hits'])) {

                $fallbackquery = [
                    'q' => $arrayKeyword[0], // Rechercher un mot-clé principal
                    'query_by' => 'keys_find', 'concept',
                    'query_by_weights' => '5,3',
                    'num_typos' => 2,
                ];
                $response = $this->client->get('collections/responses/documents/search?' . Query::build($fallbackquery));
            }

/*
            $concepts = [];
            foreach ($response['hits'] as $hit) {
                $concepts = array_merge($concepts, $hit['document']['responses'] ?? []);
            }
            return $this->filterRelevantConcepts($concepts, $keywords);
*/
            return $response['hits'][0] ?? [];

        } catch (\Exception $e) {
            return [];
        }
    }

}
