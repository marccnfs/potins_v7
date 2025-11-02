<?php

namespace App\Service;

/**
 * Fournit la liste des modèles 3D disponibles pour la création MindAR.
 *
 * @psalm-type MindArModel=array{
 *     id: string,
 *     name: string,
 *     path: string,
 *     description?: string|null,
 *     emoji?: string|null,
 * }
 */
class MindArModelLibrary
{
    /**
     * @return array<int, array{id: string, name: string, path: string, description?: string|null, emoji?: string|null}>
     */
    public function getModels(): array
    {
        return [
            [
                'id' => 'lotus',
                'name' => 'Lotus zen',
                'path' => '/build/models/lotus.glb',
                'description' => 'Un lotus flottant, idéal pour les scènes calmes ou méditatives.',
                'emoji' => '🪷',
            ],
            [
                'id' => 'rock',
                'name' => 'Roche sculptée',
                'path' => '/build/models/rock.glb',
                'description' => 'Un rocher texturé pour ancrer vos éléments dans un décor naturel.',
                'emoji' => '🪨',
            ],
            [
                'id' => 'bamboo',
                'name' => 'Forêt de bambous',
                'path' => '/build/models/bamboo.glb',
                'description' => 'Un bosquet de bambous pour ajouter une ambiance végétale et zen.',
                'emoji' => '🎋',
            ],
        ];
    }
}
