<?php

namespace App\Infrastructure\ChabotIA\Typesense;

use App\Infrastructure\ChabotIA\IndexerInterface;
use Symfony\Component\HttpFoundation\Response;

class TypesenseIndexer implements IndexerInterface
{
    private TypesenseClient $client;

    public function __construct(TypesenseClient $client)
    {
        $this->client = $client;
    }

    public function index(array $data): void
    {
        try {
            $this->client->patch("collections/ressources/documents/{$data['id']}", $data);
        } catch (TypesenseException $exception) {
            if (Response::HTTP_NOT_FOUND === $exception->status && 'Not Found' === $exception->message) {
                $this->client->post('collections', [
                    'name' => 'ressources',
                    'fields' => [
                        ['name' => 'id', 'type' => 'string'],
                        ['name' => 'title', 'type' => 'string'],
                        ['name' => 'summary', 'type' => 'string','facet' => true],
                        ['name' => 'content', 'type' => 'string'],
                        ['name' => 'category', 'type' => 'string[]'],
                        ['name' => 'info', 'type' => 'string'],
                        ['name' => 'label', 'type' => 'string', 'facet' => true],
                        ['name' => 'published_at', 'type' => 'string'],
                        ['name' => 'htlm_titre', 'type' => 'string'],
                        ['name' => 'url', 'type' => 'string'],
                    ]
                ]);
                $this->client->post('collections/ressources/documents', $data);
            } elseif (Response::HTTP_NOT_FOUND === $exception->status) {
                $this->client->post('collections/ressources/documents', $data);
            } else {
                throw $exception;
            }
        }
    }

    public function indexOne(array $data): void
    {
        try {
            $this->client->patch("collections/ressources/documents/{$data['id']}", $data);
        } catch (TypesenseException $exception) {
            if (Response::HTTP_NOT_FOUND === $exception->status && 'Not Found' === $exception->message) {
                $this->client->post('collections/ressources/documents', $data);
            } elseif (Response::HTTP_NOT_FOUND === $exception->status) {
                $this->client->post('collections/ressources/documents', $data);
            } else {
                throw $exception;
            }
        }
    }

    public function remove(string $id): void
    {
        $this->client->delete("collections/ressources/documents/$id");
    }

    public function clean(): void
    {
        try {
            $this->client->delete('collections/ressources');
        } catch (TypesenseException $e) {
        }
    }
}
