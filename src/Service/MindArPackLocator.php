<?php
// src/Service/MindArPackLocator.php
namespace App\Service;

use App\Entity\Games\ArPack;
use Doctrine\ORM\EntityManagerInterface;

class MindArPackLocator
{
    public function __construct(private EntityManagerInterface $em, private string $publicDir) {}

    /**
     * Récupère tous les packs AR disponibles.
     * Retourne un tableau prêt à être affiché ou exposé en API.
     */
    public function getPacks(): array
    {
        $packs = $this->em->getRepository(ArPack::class)->findAll();
        return array_map(fn(ArPack $pack) => $this->formatPack($pack), $packs);
    }

    /**
     * Vérifie si un pack existe et renvoie son chemin complet (.mind)
     */
    public function resolve(string $packName): ?string
    {
        $pack = $this->em->getRepository(ArPack::class)->findOneBy(['name' => $packName]);
        if (!$pack) {
            return null;
        }

        $filePath = $this->publicDir . $pack->getMindPath();
        return file_exists($filePath) ? $filePath : null;
    }

    /**
     * Pour affichage côté front : retourne le JSON de description d’un pack.
     */
    public function getPackData(string $packName): ?array
    {
        $pack = $this->em->getRepository(ArPack::class)->findOneBy(['name' => $packName]);
        if (!$pack) {
            return null;
        }

        return $this->formatPack($pack);
    }

    private function formatPack(ArPack $pack): array
    {

        $data = [
            'name' => $pack->getName(),
            'mindPath' => $pack->getMindPath(),
            'jsonPath' => $pack->getPathJson(),
            'thumbnail' => $pack->getThumbnail(),
        ];

        $metadata = $this->extractMetadata($pack);
        if (!empty($metadata)) {
            $data['metadata'] = $metadata;
        }

        return $data;
    }

    private function extractMetadata(ArPack $pack): ?array
    {
        $jsonPath = $pack->getPathJson();
        if (!$jsonPath) {
            return null;
        }

        $fullPath = $this->publicDir . $jsonPath;
        if (!is_file($fullPath)) {
            return null;
        }

        $content = @file_get_contents($fullPath);
        if ($content === false) {
            return null;
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            return null;
        }

        return $this->normalizeMetadata($decoded);
    }

    private function normalizeMetadata(array $data): ?array
    {
        $items = [];

        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $items = $this->extractItems($data['metadata']);
        } else {
            $items = $this->extractItems($data);
        }

        if (empty($items)) {
            return null;
        }

        return ['items' => $items];
    }

    private function extractItems(array $data): array
    {
        $rawItems = [];

        if (isset($data['items']) && is_array($data['items'])) {
            $rawItems = $data['items'];
        } elseif (isset($data['targets']) && is_array($data['targets'])) {
            $rawItems = $data['targets'];
        } elseif ($this->isList($data)) {
            $rawItems = $data;
        }

        $items = [];
        foreach ($rawItems as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $items[] = [
                'index' => (int)($item['index'] ?? $index),
                'label' => $item['label']
                    ?? $item['name']
                        ?? $item['id']
                        ?? sprintf('Cible %d', $index + 1),
                'thumb' => $item['thumb']
                    ?? $item['thumbnail']
                        ?? $item['image']
                        ?? $item['url']
                        ?? null,
                'image' => $item['image']
                    ?? $item['source']
                        ?? $item['thumb']
                        ?? null,
            ];
        }

        return array_values(array_filter($items, fn(array $item) => !empty($item['thumb']) || !empty($item['image'])));
    }

    private function isList(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        $expectedKeys = range(0, count($value) - 1);
        return array_keys($value) === $expectedKeys;
    }
}
