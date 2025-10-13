<?php

namespace App\Service;

use Symfony\Component\Process\Process;

class MindArTargetBuilder
{
    public function build(string $imagePath, string $destMindPath): bool
    {
        // Exemple d’appel d’un script Node qui encapsule la lib MindAR
        $cmd = ['node', __DIR__.'/../../tools/mindar/build-mind.js', $imagePath, $destMindPath];
        $process = new Process($cmd, timeout: 60);
        $process->run();
        return $process->isSuccessful();
    }
}
