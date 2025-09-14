<?php


namespace App\Service;


use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientApiIaTools
{

    private HttpClientInterface $client;
    private string $apiUrl;
    private string $apiToken;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->apiUrl = 'https://or4.fr/api/ai_tools'; // URL de l'API Platform
        $this->apiToken = 'TON_SECRET_API_KEY'; // Facultatif si API publique
    }

    public function getAiTools(): array
    {
        try {
            $response = $this->client->request('GET', $this->apiUrl, [
                'headers' => [
                    'Accept' => 'application/ld+json',
                    'X-TOKEN' => $this->apiToken,
                ],
            ]);

            return $response->toArray(); // Convertit JSON en tableau PHP
        } catch (\Exception $e) {
            return ['error' => 'Impossible de récupérer les données : ' . $e->getMessage()];
        }
    }


    //todo récupération d'un IAtools avec son article
       // public function getAiTool($id): bool|array
       // {

       // }




}