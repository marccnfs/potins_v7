<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class MindArTargetBuilder
{
    public function __construct(private string $projectDir) {}

    public function build(string $imagesDir, string $outputDir): bool
    {
        $fs = new Filesystem();
        if (!$fs->exists($imagesDir)) {
            throw new \RuntimeException("Dossier d’images introuvable : $imagesDir");
        }

        $fs->mkdir($outputDir);

        $cmd = [
            'npx', 'mindar-image-cli',
            '-i', $imagesDir,
            '-o', "$outputDir/targets.mind",
            '--json'
        ];

        $process = new Process($cmd, $this->projectDir);
        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException("Erreur MindAR : ".$process->getErrorOutput());
        }

        return file_exists("$outputDir/targets.mind");
    }
}
