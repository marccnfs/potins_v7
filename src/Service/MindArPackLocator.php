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

        return array_map(fn(ArPack $p) => [
            'name' => $p->getName(),
            'mindPath' => $p->getMindPath(),
            'jsonPath' => $p->getJsonPath(),
            'thumbnail' => $p->getThumbnail(),
        ], $packs);
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
        if (!$pack) return null;

        $data = [
            'name' => $pack->getName(),
            'mindPath' => $pack->getMindPath(),
            'jsonPath' => $pack->getJsonPath(),
            'thumbnail' => $pack->getThumbnail(),
        ];

        if ($pack->getJsonPath()) {
            $fullJson = $this->publicDir . $pack->getJsonPath();
            if (file_exists($fullJson)) {
                $data['metadata'] = json_decode(file_get_contents($fullJson), true);
            }
        }

        return $data;
    }
}
