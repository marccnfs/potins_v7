<?php

namespace App\Controller\ChabotAI;

use App\Classe\PublicSession;
use App\Infrastructure\Responses\SearchInterface;
use App\Lib\Links;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatBotFulloldv2Controller extends AbstractController
{

use PublicSession;


    #[Route('/oldv2/chatbot/full', name:"oldv2-chatbot_full")]
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

    #[Route('/oldv2/api/chatbot/search', name: 'oldv2-chatbot_search', methods: ['POST'])]
    public function search(Request $request, SearchInterface $search): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $question = $data['question'] ?? '';

        // Étape par étape
        $result_extract = $this->extractKeywords($question);
        $keywords=$result_extract['keywords'] ?? [];

        // todo Vérifie que le tableau $keywords n'est pas vide

        $stringKeywords=$this->extractKeywordsToString($result_extract);
        $arrayKeyword=$this->getArrayKeywordByScore($result_extract);
        $topKeywords = $this->getTopKeywords($result_extract, 2);
        //dump($stringKeywords,$arrayKeyword, $topKeywords);

        $conceptResponses = $search->searchConceptResponses($stringKeywords, (array)$arrayKeyword);

        //dump($conceptResponses);
        $specificData = "";//$this->querySpecificData($keywords);

        return new JsonResponse([
            'steps' => [
                'Identification des mots-clés',
                'Analyse des concepts associés',
                'Recherche des définitions',
                'Assemblage de la réponse',
            ],
            /*'keywords' => json_encode($arrayKeyword, JSON_UNESCAPED_UNICODE),
            'related_concepts' => json_encode($conceptResponses['document'], JSON_UNESCAPED_UNICODE),
            'response' => $this->synthesizeResponse($conceptResponses['document']),
            'type'=>$conceptResponses['document']['type'] ?? ""
            */
            'keywords' => $this->extractOnlyKeywords($result_extract),
            'related_concepts' => $conceptResponses['document'],
            'response' => $this->synthesizeResponse($conceptResponses['document']),
            'type'=>$conceptResponses['document']['type'] ?? ""
        ]);

    }

    private function extractKeywords(string $question): array
    {
        // Chemin absolu du script Python
        $scriptPathAbs = 'D:\potinsnumeriques\Python3\analyse_keyword.py';
        $command = sprintf(
            "python %s %s",
            escapeshellarg($scriptPathAbs),
            escapeshellarg($question)
        );
        $output = shell_exec($command);

        //file_put_contents('../../.../public/debug_output.log', $output);

        // Nettoyer la sortie pour qu'elle soit un JSON valide
        $cleaned_output = trim($output); // Supprime les espaces ou sauts de ligne autour
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
        return $result ?? [];
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


    public function extractKeywordsToString(array $keywords): string
    {
        // Vérifier que la clé "keywords" existe dans le tableau
        if (!isset($keywords['keywords']) || !is_array($keywords['keywords'])) {
            throw new \InvalidArgumentException('La structure du tableau est invalide : clé "keywords" manquante ou incorrecte.');
        }

        // Extraire les mots-clés depuis la clé "keyword" de chaque élément
        $extractedKeywords = array_map(function ($keywordData) {
            return $keywordData['keyword']; // Récupère le mot-clé
        }, $keywords['keywords']);

        // Combiner les mots-clés en une chaîne
        return implode(' ', $extractedKeywords);
    }


    public function extractOnlyKeywords(array $keywords): array
    {
        // Vérifie que la clé "keywords" existe et est un tableau
        if (!isset($keywords['keywords']) || !is_array($keywords['keywords'])) {
            throw new \InvalidArgumentException('La structure du tableau est invalide : clé "keywords" manquante ou incorrecte.');
        }

        // Extraire uniquement les mots-clés depuis la clé "keyword"
        return array_map(function ($keywordData) {
            return $keywordData['keyword']; // Récupère le mot-clé
        }, $keywords['keywords']);
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
