<?php

namespace App\Service;

class MindArPackLocator
{
    public function __construct(private string $projectDir) {}

    public function listPacks(): array
    {
        $base = $this->projectDir.'/public/mindar/packs';
        if (!is_dir($base)) return [];
        $packs = [];
        foreach (scandir($base) as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            $json = $base."/$dir/targets.json";
            if (is_file($json)) {
                $data = json_decode(file_get_contents($json), true) ?: [];
                $packs[] = [
                    'name' => $data['name'] ?? $dir,
                    'pathMind' => "/mindar/packs/$dir/targets.mind",
                    'items' => $data['items'] ?? [],
                ];
            }
        }
        return $packs;
    }
}
