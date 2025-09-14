<?php

namespace Command;


use App\Infrastructure\Search\IndexerInterface;
use App\Repository\BoardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IndexCommand extends Command
{
    protected static $defaultName = 'app:index';
    private IndexerInterface $indexer;
    private NormalizerInterface $normalizer;
    private EntityManagerInterface $em;
    private BoardRepository $webrepo;

    public function __construct(
        IndexerInterface $indexer,
        EntityManagerInterface $em,
        NormalizerInterface $normalizer,
        BoardRepository $websiteRepository
    ) {
        parent::__construct();
        $this->indexer = $indexer;
        $this->em = $em;
        $this->normalizer = $normalizer;
        $this->webrepo=$websiteRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $io->progressStart();
        $this->indexer->clean();

        // On importe les websites

        $items = $this->webrepo->findForIndex();

        foreach ($items as $item) {
            $io->progressAdvance();
            $this->indexer->index((array) $this->normalizer->normalize($item, 'search'));

            $this->em->clear();
        }

        $io->progressFinish();
        $io->success('Les contenus ont bien été indexés');

        return 0;
    }
}
