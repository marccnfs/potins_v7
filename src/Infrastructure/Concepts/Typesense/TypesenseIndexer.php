<?php

namespace App\Infrastructure\Concepts\Typesense;

use App\Infrastructure\Concepts\IndexerInterface;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

class TypesenseIndexer implements IndexerInterface
{
    private TypesenseClient $client;
    private LoggerInterface $logger;

    public function __construct(TypesenseClient $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger=$logger;
    }

    public function index(array $data): void
    {
        try {
            $this->client->patch("collections/concepts/documents/{$data['id']}", $data);
        } catch (TypesenseException $exception) {
            $this->logger->error("Erreur lors de l'indexation de l'élément " . $data['id'] . " : " . $exception->getMessage());
            if (Response::HTTP_NOT_FOUND === $exception->status && 'Not Found' === $exception->message) {
                $this->client->post('collections', [
                    'name' => 'concepts',
                    'fields' => [
                        ['name' => 'id', 'type' => 'string'],
                        ['name' => 'keyword', 'type' => 'string'],
                        ['name' => 'concepts', 'type' => 'string[]'],
                        ['name' => 'definition', 'type' => 'string'],
                    ]
                ]);
                $this->client->post('collections/concepts/documents', $data);
            } elseif (Response::HTTP_NOT_FOUND === $exception->status) {
                $this->client->post('collections/concepts/documents', $data);
            } else {
                throw $exception;
            }
        }
    }

    public function indexOne(array $data): void
    {
        try {
            $this->client->patch("collections/concepts/documents/{$data['id']}", $data);
        } catch (TypesenseException $exception) {
            if (Response::HTTP_NOT_FOUND === $exception->status && 'Not Found' === $exception->message) {
                $this->client->post('collections/concepts/documents', $data);
            } elseif (Response::HTTP_NOT_FOUND === $exception->status) {
                $this->client->post('collections/concepts/documents', $data);
            } else {
                throw $exception;
            }
        }
    }

    public function remove(string $id): void
    {
        $this->client->delete("collections/concepts/documents/$id");
    }

    public function clean(): void
    {
        try {
            $this->client->delete('collections/concepts');
        } catch (TypesenseException $e) {
        }
    }
}
