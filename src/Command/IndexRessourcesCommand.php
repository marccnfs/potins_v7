<?php

// src/Command/IndexRessourcesCommand.php
namespace App\Command;


use App\Infrastructure\ChabotIA\IndexerInterface;
use App\Repository\RessourcesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsCommand(name: 'app:index-ressources')]
class IndexRessourcesCommand extends Command
{
    public function __construct(
        private IndexerInterface $indexer,
        private NormalizerInterface $normalizer,
        private EntityManagerInterface $em,
        private RessourcesRepository $ressourcesRepo,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $output->writeln([
            'Ressources indexation globale',
            '============',
            '',
        ]);

        $config = $this->em->getConnection()->getConfiguration();
        // Supposons qu’un middleware de logging était présent, on le supprime :
        $config->setMiddlewares([]);

        $io->progressStart();
        $this->indexer->clean();

        // On importe les ressources

        $items = $this->ressourcesRepo->findForIndex();

        foreach ($items as $item) {
            $io->progressAdvance();
            $this->indexer->index((array) $this->normalizer->normalize($item, 'search'));
            $this->em->clear();
        }

        $io->progressFinish();
        $io->success('Les ressources ont bien été indexés');
        $output->write('You are about to ');
        $output->write('mise a jour ressources.');

        return Command::SUCCESS;
    }
}
