<?php

// src/Command/IndexResponsesCommand.php
namespace App\Command;


use App\Infrastructure\Responses\IndexerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Psr\Log\LoggerInterface;

#[AsCommand(name: 'app:index-responses')]
class IndexResponsesCommand extends Command
{
    public function __construct(
        private IndexerInterface $indexer,
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //$this->logger->info('Début de l’indexation des données.');

        $io = new SymfonyStyle($input, $output);
        $output->writeln([
            'Responses indexation globale',
            '============',
            '',
        ]);

        $io->progressStart();
        $this->indexer->clean();

        // Chemin vers le fichier JSON
        $filePath = __DIR__ . '/keyword_concept_definition.json';

        try {
            // Charger le contenu du fichier
            if (!file_exists($filePath)) {
                throw new FileNotFoundException("Le fichier JSON est introuvable : $filePath");
            }

            $jsonContent = file_get_contents($filePath);

            try {
            // Décoder le JSON en tableau PHP
            $items = json_decode($jsonContent, true);


                // Vérification d'erreur
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Erreur de décodage JSON : ' . json_last_error_msg());
                }

                foreach ($items as $item) {
                    //$this->logger->info("Indexation de l’élément : " . $item['id']);
                    $io->progressAdvance();
                    $this->indexer->index($item);
                }

            } catch (\Exception $e) {
                //$this->logger->error("Erreur lors de l’exécution : " . $e->getMessage());
                return Command::FAILURE;
            }


            $io->progressFinish();
            $io->success('Les concepts_responses ont bien été indexés');
            $output->write('You are about to ');
            $output->write('mise a jour ressources.');
            $this->logger->info('Fin de l’indexation.');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            //$this->logger->error("Erreur lors du chargement du fichier : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
