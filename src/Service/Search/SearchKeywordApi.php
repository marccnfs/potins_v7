<?php


namespace App\Service\Search;


use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SearchKeywordApi
{

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function extractKeywords($question): mixed
    {
        $client = HttpClient::create();

        $response = $client->request(
            'POST',
            'https://python-api.potinsnumeriques.fr/analyze',  // Remplacez par l'URL de votre API
            [
                'json' => ['question' => $question]
            ]
        );

        if ($response->getStatusCode()==200){
            if($response->getContent()){
                return $response->toArray();
            }else{
                return [];
            }
        }else{
            return [];
        }
    }

}