<?php

namespace Command;

use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class MajPostCommand extends Command
{
    protected static $defaultName = 'app:majpost';
    private EntityManagerInterface $em;
    private PostRepository $postRepository;



    public function __construct(
        EntityManagerInterface $em,
        PostRepository $postRepository
    )
    {
        parent::__construct();
        $this->em = $em;
        $this->postRepository=$postRepository;

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        //$this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $io->progressStart();
        $items = $this->postRepository->findAllAndWebsite();

        foreach ($items as $item) {
            $io->progressAdvance();

            if (!$item->getKeymodule()){
                $item->setKeymodule($item->getWebsite()->getCodesite());
            }
            if (!$item->getLocalisation()){
                $item->setLocalisation($item->getWebsite()->getLocality());
                }
            $this->em->persist($item);
            $this->em->flush();
            }
        $io->progressFinish();
        $io->success('Les posts ont ete mis à jour');

        return 0;
    }

}