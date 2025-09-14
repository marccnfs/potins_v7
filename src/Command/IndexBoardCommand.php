<?php

namespace App\Command;


use App\Infrastructure\Search\IndexerInterface;
use App\Repository\BoardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsCommand(name: 'app:indexboard')]
class IndexBoardCommand extends Command
{
    private IndexerInterface $indexer;
    private NormalizerInterface $normalizer;
    private EntityManagerInterface $em;
    private BoardRepository $bdbrepo;

    public function __construct(
        IndexerInterface $indexer,
        EntityManagerInterface $em,
        NormalizerInterface $normalizer,
        BoardRepository $boardRepository
    ) {
        parent::__construct();
        $this->indexer = $indexer;
        $this->em = $em;
        $this->normalizer = $normalizer;
        $this->bdbrepo=$boardRepository;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $io->progressStart();

        $this->indexer->clean();

        // On importe les websites

        $items = $this->bdbrepo->findForIndex();

        foreach ($items as $item) {
            $io->progressAdvance();
           // dump($this->normalizer->normalize($item, 'search'));
           // $test=$tes;
            $this->indexer->index((array) $this->normalizer->normalize($item, 'search'));

            $this->em->clear();
        }

        $io->progressFinish();
        $io->success('Les contenus ont bien été indexés');

        return 0;
    }
}
