<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PythonShellService
{
    private string $pythonPath;
    private string $scriptPath;

    public function __construct(string $pythonPath,string $scriptPath, private LoggerInterface $logger)
    {
        $this->pythonPath = $pythonPath;
        $this->scriptPath = $scriptPath;

    }

    public function analyzeQuestion(string $question): string
    {
        $startTime = microtime(true);

        $process = new Process([$this->pythonPath, $this->scriptPath, $question]);
        $process->run();

        $executionTime = microtime(true) - $startTime;
        $this->logger->info('Python script executed', [
            'question' => $question,
            'execution_time' => $executionTime,
            'output' => $process->getOutput()
        ]);

        if (!$process->isSuccessful()) {
            $this->logger->error('Python script failed', [
                'error' => $process->getErrorOutput()
            ]);
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}
