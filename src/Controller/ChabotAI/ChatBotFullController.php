<?php

namespace App\Controller\ChabotAI;

use App\Classe\PublicSession;
use App\Infrastructure\Responses\SearchInterface;
use App\Lib\Links;
use App\Service\PythonShellService;
use App\Service\Search\SearchKeywordApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;


class ChatBotFullController extends AbstractController
{

use PublicSession;

    private PythonShellService $pythonShellService;


    #[Route('/chatbot/full', name:"chatbot_full")]
    public function chatBotFull(): Response
    {

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            'chatbotfull',
            0,
            "nocity");

        return $this->render($this->useragentP.'ptn_ia/home.html.twig', [
            'directory'=>'IA',
            'customer'=>$this->customer,
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('/api/chatbot/analyze', name: 'chatbot_analyze', methods: ['POST'])]
    public function analyze(Request $request, SearchInterface $search, PythonShellService $pythonShellService, CacheInterface $cache): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $question = $data['question'] ?? '';

        if (empty($question)) {
            return new JsonResponse(['error' => 'Question non fournie'], 400);
        }

        try {
            $cacheKey = 'analyze_' . md5($question);
            $output = $cache->get($cacheKey, function () use ($pythonShellService, $question) {
                return $pythonShellService->analyzeQuestion($question);
            });
            $results=$this->extractKeywordsV1($output);
            $stringKeywords = $this->extractKeywordsToString($results);
            $arrayKeyword = $this->extractOnlyKeywords($results);

            $conceptResponses = $search->searchConceptResponses($stringKeywords, $arrayKeyword);

            return new JsonResponse([
                'steps' => [
                    'Identification des mots-clés',
                    'Analyse des concepts associés',
                    'Recherche des définitions',
                    'Assemblage de la réponse',
                ],
                'keywords' => $arrayKeyword,
                'related_concepts' => $conceptResponses['document'],
                'response' => $this->synthesizeResponse($conceptResponses['document']),
                'type' => $conceptResponses['document']['type'] ?? ""
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }


    #[Route('/api/chatbot/analyse-v1', name: 'chatbot_search_analyse-v1', methods: ['POST'])]
    public function searchV1(Request $request, SearchKeywordApi $searchKeywordApi,SearchInterface $search): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $question = $data['question'] ?? '';

        if (!$question) {
            return $this->json(['error' => 'La question est manquante'], Response::HTTP_BAD_REQUEST);
        }

        $keywords=$searchKeywordApi->extractKeywords($question);
        dump($keywords);
        $stringKeywords = $this->extractKeywordsToString($keywords);
        $arrayKeyword = $this->getArrayKeywordByScore($keywords);
        $topKeywords = $this->getTopKeywords($keywords, 2);

        $conceptResponses = $search->searchConceptResponses($stringKeywords, (array)$arrayKeyword);


        $specificData = "";//$this->querySpecificData($keywords);

        return new JsonResponse([
            'steps' => [
                'Identification des mots-clés',
                'Analyse des concepts associés',
                'Recherche des définitions',
                'Assemblage de la réponse',
            ],
            'keywords' => $this->extractOnlyKeywords($keywords),
            'related_concepts' => $conceptResponses['document'],
            'response' => $this->synthesizeResponse($conceptResponses['document']),
            'type' => $conceptResponses['document']['type'] ?? ""
        ]);

    }


    public function getTopKeywords(array $keywords, int $limit = 1): array
    {
        // Vérifie que la clé "keywords" existe et est un tableau
        if (!isset($keywords['keywords']) || !is_array($keywords['keywords'])) {
            throw new \InvalidArgumentException('La structure du tableau est invalide : clé "keywords" manquante ou incorrecte.');
        }

        // Trie les mots-clés par score décroissant
        usort($keywords['keywords'], function ($a, $b) {
            return $b['final_score'] <=> $a['final_score']; // Ordre décroissant
        });

        // Retourne les $limit meilleurs mots-clés
        return array_slice($keywords['keywords'], 0, $limit);
    }

    public function getArrayKeywordByScore(array $keywords): ?array
    {
        // Vérifie que la clé "keywords" existe et est un tableau
        if (!isset($keywords['keywords']) || !is_array($keywords['keywords'])) {
            throw new \InvalidArgumentException('La structure du tableau est invalide : clé "keywords" manquante ou incorrecte.');
        }

        // Vérifie que le tableau n'est pas vide
        if (empty($keywords['keywords'])) {
            return null; // Aucun mot-clé
        }

        // Trouve le mot-clé avec le meilleur score
        $bestKeyword = array_reduce($keywords['keywords'], function ($carry, $item) {
            if ($carry === null || $item['final_score'] > $carry['final_score']) {
                return $item; // Met à jour si le score est plus élevé
            }
            return $carry;
        });

        // Retourne le mot-clé avec le meilleur score ou null
        return $bestKeyword ?? null;
    }


    public function extractKeywordsToString(array $data): string
    {
        // Vérifie que la clé "keywords" existe et est un tableau
        if (!isset($data['keywords']) || !is_array($data['keywords'])) {
            throw new \InvalidArgumentException('La structure du tableau est invalide : clé "keywords" manquante ou incorrecte.');
        }

        // Parcourt chaque élément sous "keywords" et récupère le mot-clé
        $extractedKeywords = [];
        foreach ($data['keywords'] as $keywordData) {
            if (!isset($keywordData['keyword'])) {
                throw new \InvalidArgumentException('La structure des données est invalide : clé "keyword" manquante.');
            }
            $extractedKeywords[] = $keywordData['keyword']; // Récupère le mot-clé
        }

        // Combine tous les mots-clés en une chaîne séparée par des espaces
        return implode(' ', $extractedKeywords);
    }


    public function extractOnlyKeywords(array $data): array
    {
        // Vérifie que la clé "keywords" existe et est un tableau
        if (!isset($data['keywords']) || !is_array($data['keywords'])) {
            throw new \InvalidArgumentException('La structure du tableau est invalide : clé "keywords" manquante ou incorrecte.');
        }

        // Parcourt chaque élément sous "keywords" et récupère le mot-clé
        $extractedKeywords = [];
        foreach ($data['keywords'] as $keywordData) {
            if (!isset($keywordData['keyword'])) {
                throw new \InvalidArgumentException('La structure des données est invalide : clé "keyword" manquante.');
            }
            $extractedKeywords[] = $keywordData['keyword']; // Récupère le mot-clé
        }

        // Retourne un tableau des mots-clés
        return $extractedKeywords;
    }


    private function extractKeywordsV1(string $response): array
    {
        $cleaned_output = trim($response); // Supprime les espaces ou sauts de ligne autour
        $cleaned_output = preg_replace('/^b?"""|"""$/', '', $cleaned_output); // Supprime les triples guillemets
        $cleaned_output = utf8_encode($cleaned_output); // Forcer l'encodage UTF-8
        // Filtrer uniquement le JSON valide
        $start = strpos($cleaned_output, '{'); // Trouver le début du JSON
        $end = strrpos($cleaned_output, '}'); // Trouver la fin du JSON

        if ($start !== false && $end !== false) {
            $cleaned_output = substr($cleaned_output, $start, $end - $start + 1);
            $result = json_decode($cleaned_output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException("Erreur de décodage JSON : " . json_last_error_msg());
            }
        } else {
            return  ["Erreur : Impossible de trouver un JSON valide dans la sortie."];
        }

    return $result??[];

    }


    private function synthesizeResponse(array $results): string
    {
        $responseText = "Voici les informations trouvées :\n";

       // foreach ($results as $hit) {
            $item = $results;

        $responseText .= "- " . $item['description'] . "\n";
            if (!empty($item['link'])) {
                $responseText .= "  En savoir plus : " . $item['link'] . "\n";
            }
            if (!empty($item['date'])) {
                $responseText .= "  Date disponible : Oui\n";
            }
        //}

        return $responseText ?: "Je n'ai trouvé aucune information spécifique pour votre requête.";
    }
}

