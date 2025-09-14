<?php

namespace App\Controller\ChabotAI;

use App\Infrastructure\ChabotIA\SearchInterface;
use App\Repository\RessourcesRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;


class NewsController extends AbstractController
{

    private SearchInterface $search;
    private LoggerInterface $logger;

    public function __construct(SearchInterface $search, RessourcesRepository $ressourcesRepository, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->search = $search;
        $this->logger = $logger;
    }

    #[Route('/api/news/ia', name: 'api_ia_news')]
    public function getIANews(): JsonResponse
    {
        // Exemple : récupération depuis PostgreSQL ou Elasticsearch.
        // Ici, les données sont simulées.

        $articles = [
            ['title' => 'L\'IA révolutionne la santé', 'summary' => 'Les dernières avancées en IA médicale.', 'date' => '2024-06-17', 'link' => '/news/ia-sante', 'image' => '/images/ia-sante.jpg'],
            ['title' => 'OpenAI lance un nouveau modèle', 'summary' => 'Un modèle encore plus puissant.', 'date' => '2024-06-15', 'link' => '/news/openai-modele', 'image' => '/images/openai.jpg'],
            ['title' => 'L’IA et l’art : nouvelle frontière ?', 'summary' => 'Quand l’IA devient créatrice.', 'date' => '2024-06-10', 'link' => '/news/ia-art', 'image' => '/images/ia-art.jpg'],
        ];

        return $this->json($articles);
    }


    #[Route('/api/chatbot', name: 'api_chatbot', methods: ['POST'])]
    public function chatbot(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $question = $data['question'] ?? '';

        // Détection de mots-clés simples pour améliorer la recherche
        $keywords = ['OpenAI', 'tendances', 'dernières actualités'];
        $matchedKeyword = '';

        foreach ($keywords as $keyword) {
            if (stripos($question, $keyword) !== false) {
                $matchedKeyword = $keyword;
                break;
            }
        }
        $query = $matchedKeyword ?: $question;

        try {
            $searchResults = $this->search->search($query);

                                                                    /*  Première version en string
                                                                    $response = [];
                                                                     if (!empty($searchResults['hits'])) {

                                                                         foreach ($searchResults['hits'] as $hit) {
                                                                             $response[] = "👉 *" . $hit['document']['title'] . "* : " . $hit['document']['summary'] . "\n" .
                                                                                 "[Lire plus](" . $hit['document']['url'] . ")";
                                                                         }
                                                                     } else {
                                                                         $response = ['Hmm... je ne trouve rien sur ce sujet. As-tu essayé une autre formulation ?'];
                                                                     }
                                                                         */
                                                                    // Deuxième version en array
            if (!empty($searchResults['hits'])) {
                $response = array_map(function ($hit) {
                    return [
                        'title' => $hit['document']['title'],
                        'summary' => $hit['document']['summary'],
                        'link' => $hit['document']['url'],
                       // 'image' => $hit['document']['image']
                    ];
                }, $searchResults['hits']);
            } else {
                $response = [
                    [
                        'title' => 'Aucun résultat trouvé',
                        'summary' => 'Essayez une autre requête.',
                        'link' => '#',
                        'image' => '/images/placeholder.png'
                    ]
                ];

            }
        } catch (\Exception $e) {
            $response = ['Une erreur est survenue avec le moteur de recherche.'];
        }
dump($response);
        return $this->json(['response' => $response]);

    }

    #[Route('/api/feedback', name: 'api_feedback', methods: ['POST'])]
    public function feedback(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $responseText = $data['responseText'] ?? '';
        $feedback = $data['feedback'] ?? '';

        // Simuler l'enregistrement en base (ou logger pour commencer)
        $this->logger->info('Feedback reçu', [
            'responseText' => $responseText,
            'feedback' => $feedback
        ]);

        return $this->json(['message' => 'Feedback enregistré']);
    }
}