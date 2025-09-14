<?php

namespace App\Infrastructure\Search\Typesense;

use App\Infrastructure\Search\IndexerInterface;
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
            $this->client->patch("collections/boards/documents/{$data['id']}", $data);
        } catch (TypesenseException $exception) {
            if (Response::HTTP_NOT_FOUND === $exception->status && 'Not Found' === $exception->message) {
                $this->client->post('collections', [
                    'name' => 'boards',
                    'fields' => [
                        ['name' => 'id', 'type' => 'string'],
                        ['name' => 'title', 'type' => 'string'],
                        ['name' => 'content', 'type' => 'string'],
                        ['name' => 'category', 'type' => 'string[]'],
                        ['name' => 'adresses', 'type' => 'string[]','facet' => true],
                        ['name' => 'type', 'type' => 'string'],
                        ['name' => 'city', 'type' => 'string', 'facet' => true],
                        ['name' => 'created_at', 'type' => 'int64'],
                        ['name' => 'gps', 'type' => 'string'],
                        ['name' => 'url', 'type' => 'string'],
                        ['name' => 'pict', 'type' => 'string'],
                    ],
                    'default_sorting_field' => 'created_at',
                ]);
                $this->client->post('collections/boards/documents', $data);
            } elseif (Response::HTTP_NOT_FOUND === $exception->status) {
                $this->client->post('collections/boards/documents', $data);
            } else {
                throw $exception;
            }
        }
    }

    public function indexOne(array $data): void
    {
        try {
            $this->client->patch("collections/boards/documents/{$data['id']}", $data);
        } catch (TypesenseException $exception) {
            if (Response::HTTP_NOT_FOUND === $exception->status && 'Not Found' === $exception->message) {
                $this->client->post('collections/boards/documents', $data);
            } elseif (Response::HTTP_NOT_FOUND === $exception->status) {
                $this->client->post('collections/boards/documents', $data);
            } else {
                throw $exception;
            }
        }
    }

    public function remove(string $id): void
    {
        $this->client->delete("collections/boards/documents/$id");
    }

    public function clean(): void
    {
        try {
            $this->client->delete('collections/boards');
        } catch (TypesenseException $e) {
        }
    }
}
