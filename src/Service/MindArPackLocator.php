<?php

namespace App\Service;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Localise et décrit les packs MindAR (.mind + targets.json + thumbs)
 * situés dans public/mindar/packs/
 */
class MindArPackLocator
{
    private string $packsDir;
    private string $publicDir;

    public function __construct(private string $projectDir)
    {
        $this->publicDir = rtrim($this->normalizePath($this->projectDir . '/public'), '/');
        $this->packsDir = $this->publicDir . '/mindar/packs';
    }

    /**
     * Liste tous les packs MindAR disponibles.
     *
     * @return array<int, array{name: string, pathMind: string, items: array}>
     */
    public function listPacks(): array
    {
        if (!is_dir($this->packsDir)) {
            return [];
        }

        $finder = new Finder();
        $finder->directories()->in($this->packsDir)->depth('== 0');

        $packs = [];
        foreach ($finder as $dir) {
            $path = $dir->getRealPath();
            if ($path === false) {
                continue;
            }

            $path = $this->normalizePath($path);
            $jsonPath = $this->normalizePath("$path/targets.json");
            $mindPath = $this->normalizePath("$path/targets.mind");
            if (!is_file($mindPath)) {
                continue; // pas de .mind -> pas un vrai pack
            }

            $data = [];
            if (is_file($jsonPath)) {
                $data = json_decode(file_get_contents($jsonPath), true) ?: [];
            }

            $items = array_map(function (array $item) use ($path): array {
                if (!isset($item['thumb']) || !is_string($item['thumb']) || $item['thumb'] === '') {
                    return $item;
                }

                $thumb = $this->normalizePath($item['thumb']);
                if (preg_match('/^(?:[a-z]+:)?\/\//i', $thumb) === 1 || str_starts_with($thumb, '/')) {
                    $item['thumb'] = $thumb;
                    return $item;
                }

                $absoluteThumb = $this->normalizePath($path . '/' . ltrim($thumb, '/'));
                $item['thumb'] = $this->toPublicPath($absoluteThumb);

                return $item;
            }, $data['items'] ?? []);

            $packs[] = [
                'name'      => $data['name'] ?? $dir->getBasename(),
                'pathMind'  => $this->toPublicPath($mindPath),
                'items'     => $items,
                'dir'       => $dir->getBasename(),
                'json'      => $jsonPath,
            ];

        }
        usort($packs, static fn (array $a, array $b) => strnatcasecmp($a['name'], $b['name']));

        return $packs;
    }

    /**
     * Récupère un pack spécifique par nom ou dossier.
     *
     * @param string $name Nom du pack (ex: "zen-demo")
     * @return array{name: string, pathMind: string, items: array, dir: string}
     */
    public function getPack(string $name): array
    {
        foreach ($this->listPacks() as $pack) {
            if ($pack['name'] === $name || $pack['dir'] === $name) {
                return $pack;
            }
        }

        // Si aucun pack trouvé : on jette une exception HTTP lisible
        throw new NotFoundHttpException(sprintf('Pack MindAR "%s" introuvable dans %s', $name, $this->packsDir));
    }

    /**
     * Vérifie l'existence d'un pack (utile pour debug ou pré-check)
     */
    public function hasPack(string $name): bool
    {
        try {
            $this->getPack($name);
            return true;
        } catch (NotFoundHttpException) {
            return false;
        }
    }

    /**
     * Retourne le chemin absolu du dossier des packs
     */
    public function getBaseDir(): string
    {
        return $this->packsDir;
    }

    private function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    private function toPublicPath(string $absolutePath): string
    {
        $normalized = $this->normalizePath($absolutePath);
        $relative = str_replace($this->publicDir, '', $normalized);
        $relative = '/' . ltrim($relative, '/');

        return $relative;
    }
}
