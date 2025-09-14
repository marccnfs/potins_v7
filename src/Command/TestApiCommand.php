<?php

namespace App\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


#[AsCommand(name: 'app:test-api')]
class TestApiCommand extends Command
{

    private HttpClientInterface $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        private LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Test the chatbot analyze API');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

            try {
            $this->logger->info('Début du test.');

            $response = $this->httpClient->request('POST', 'http://127.0.0.1:8000/api/chatbot/analyze', [
                'json' => [
                    'question' => 'Quand auront lieu les prochains ateliers des Potins Numériques ?',
                ],
            ]);
            $output->writeln($response->getContent());
            return Command::SUCCESS;
        } catch (\Exception $e) {
             $this->logger->error("Erreur lors de la requette hhtp : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}


