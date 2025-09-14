<?php

namespace App\Controller\ChabotAI;

use App\Classe\PublicSession;
use App\Infrastructure\Concepts\SearchInterface;
use App\Lib\Links;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatBotFullOldController extends AbstractController
{

use PublicSession;


    #[Route('old/chatbot/full', name:"old_chatbot_full")]
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

    private function extractKeywords(string $question): array
    {

        // Chemin absolu du script Python
        $scriptPathAbs = 'D:\potinsnumeriques\Python3\analyse_questions_only.py';
        $command = sprintf(
            "python %s %s",
            escapeshellarg($scriptPathAbs),
            escapeshellarg($question)
        );
        $output = shell_exec($command);
        // Nettoyer la sortie pour qu'elle soit un JSON valide
        $cleanOutput = trim($output); // Supprime les espaces ou sauts de ligne autour
        $cleanOutput = preg_replace('/^b?"""|"""$/', '', $cleanOutput); // Supprime les triples guillemets
        $cleanOutput = utf8_encode($cleanOutput); // Forcer l'encodage UTF-8
        // Décoder la sortie JSON
        $result = json_decode($cleanOutput, true);

        // Debug pour vérifier la sortie propre et le résultat
        dump($cleanOutput); // Affiche la sortie nettoyée
        dump($result);      // Affiche le tableau décodé

        // Gérer les erreurs de décodage JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            dump(json_last_error_msg()); // Affiche le message d'erreur JSON
            throw new \RuntimeException("Erreur de décodage JSON : " . json_last_error_msg());
        }

        return $result['keywords'] ?? [];
    }

    #[Route('old/api/chatbot/search', name: 'old_chatbot_search', methods: ['POST'])]
    public function search(Request $request, SearchInterface $search): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $question = $data['question'] ?? '';

        // Étape par étape
        $keywords = $this->extractKeywords($question);
        dump($keywords);
        $filteredConcepts = $search->searchConcepts($keywords);
        $concept = $this->getResponseFromConcepts($filteredConcepts);

        dump($filteredConcepts);
        if ($concept === null) {
            $concept = "Je n'ai pas trouvé de réponse précise, mais voici des suggestions : " . implode(', ', $filteredConcepts);
        }
        dump($concept);
        $definitions = $search->fetchDefinitions($concept);
        $specificData = "";//$this->querySpecificData($keywords);
        $response = $this->synthesizeResponse($keywords, $concept, $definitions, $specificData);

        return new JsonResponse([
            'steps' => [
                'Identification des mots-clés',
                'Analyse des concepts associés',
                'Recherche des définitions',
                'Assemblage de la réponse',
            ],
            'keywords' => $keywords,
            'related_concepts' => $concept,
            'definitions' => $definitions,
            'response' => $response,
        ]);
    }

    private function getResponseFromConcepts(array $concepts): ?string
    {
        $responses = json_decode(file_get_contents('D:\potinsnumeriques\Python3\concepts_responses.json'), true);

        foreach ($concepts as $concept) {
            if (isset($responses[$concept])) {
                return $responses[$concept];
            }
        }

        return null;
    }

    #[Route('old/api/chatbot/analyze', name: 'old_chatbot_analyze', methods: ['POST'])]
    public function analyzeQuestion(Request $request, SearchInterface $search): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $question = $data['question'] ?? '';

        $keywords = $this->extractKeywords($question);

        $keyword_question=implode(" ",$keywords);
        try {
            $response = $search->search($keyword_question);
        // Traiter les résultats pour les regrouper et dédupliquer
            $processedResults = $this->processResults($response['hits'] ?? []);




        /*
        foreach ($keywords as $keyword) {
            try {
                // Recherche dans Typesense
                $searchResults = $search->search($keyword);

                if (!empty($searchResults['hits'])) {
                    foreach ($searchResults['hits'] as $hit) {
                        $relatedConcepts = array_merge($relatedConcepts, $hit['document']['concepts']);
                        // Ajouter la définition si elle est disponible
                        if (!empty($hit['document']['definition'])) {
                            $definitions[$keyword] = $hit['document']['definition'];
                        }
                    }
                }
            } catch (\Exception $e) {
                return $this->json(['error' => 'Erreur Typesense', 'details' => $e->getMessage()], 500);
            }
        }
        // Supprimer les doublons
        $relatedConcepts = array_unique($relatedConcepts);;
        // Ajouter les concepts associés au résultat
        $result['related_concepts'] = array_values($relatedConcepts) ?: [];
        $result['definitions'] = $definitions; // Inclure les définitions dans la réponse JSON
        $result['steps'][] = "Concepts associés trouvés : " . implode(', ', $relatedConcepts);
        $result['response'] = "La question concerne " . implode(', ', $keywords) . ". Concepts associés : " . implode(', ', $relatedConcepts);

        // Définir un type basé sur le premier concept associé (si disponible)
        $result['type'] = $relatedConcepts[0] ?? '';

        return $this->json($result);
        */
            return new JsonResponse([
                'keywords' => $processedResults['keywords'],
                'related_concepts' => $processedResults['concepts'],
                'definitions' => $processedResults['definitions'],
                'response' => $processedResults['response'],
            ]);
        } catch (\Exception $e) {
    return new JsonResponse(['error' => $e->getMessage()], 500);
}

    }

    #[Route('old/api/chatbot/concepts', name: 'old_chatbot_concepts', methods: ['POST'])]
    public function fetchConcepts(Request $request,SearchInterface $search): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $keyword = $data['keyword'] ?? '';

        try {
            $results = $search->search($keyword);
            $concepts = $results['hits'][0]['document']['concepts'] ?? [];

            return $this->json(['concepts' => $concepts]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de la recherche de concepts'], 500);
        }
    }

    #[Route('old/api/chatbot/progress', name: 'old_chatbot_progress', methods: ['POST'])]
    public function chatbotProgress(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $question = $data['question'] ?? '';

        // Simuler des étapes de progression
        $steps = [
            'Analyse des mots-clés dans la question.',
            'Recherche de concepts similaires dans le lexique.',
            'Établissement des liens entre les concepts.',
            'Assemblage de la réponse finale.'
        ];

        return $this->json([
            'question' => $question,
            'steps' => $steps
        ]);
    }

    private function processResults(array $results): array
    {
        $keywords = [];
        $concepts = [];
        $definitions = [];
        $responseTexts = [];

        foreach ($results as $result) {
            $document = $result['document'];

            // Ajouter les mots-clés
            if (isset($document['keyword'])) {
                $keywords[] = $document['keyword'];
            }

            // Ajouter les concepts associés
            if (isset($document['concepts'])) {
                $concepts = array_merge($concepts, $document['concepts']);
            }

            // Ajouter les définitions
            if (isset($document['keyword']) && isset($document['definition'])) {
                $definitions[$document['keyword']] = $document['definition'];
            }

            // Ajouter les réponses (facultatif pour plus de flexibilité)
            if (isset($document['response_text'])) {
                $responseTexts[] = $document['response_text'];
            }
        }

        return [
            'keywords' => array_unique($keywords),
            'concepts' => array_unique($concepts),
            'definitions' => $definitions,
            'response' => implode(' ', $responseTexts) ?: 'Résultats trouvés.',
        ];
    }

    private function synthesizeResponse(array $results, array $keywords, string $concept, string $definition, ?string $specificData): string
    {
        $response = "Voici les informations trouvées :\n";

        $response .= "- Mots-clés identifiés : " . implode(", ", $keywords) . "\n";
        $response .= "- Concept associés : " .  $concept. "\n";

        if ($specificData) {
            $response .= "- Les prochains potins numériques auront lieu le : $specificData\n";
        } else {
            $response .= "- Aucune date trouvée pour les prochains événements.\n";
        }

        if (!empty($definition)) {
                $response .= " $definition\n";
        }

        return $response;
    }


}
