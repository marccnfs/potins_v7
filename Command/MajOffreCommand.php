<?php

namespace Command;


use App\Repository\OffresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class MajOffreCommand extends Command
{
    protected static $defaultName = 'app:majoffre';
    private EntityManagerInterface $em;
    private OffresRepository $offreRepository;



    public function __construct(
        EntityManagerInterface $em,
        OffresRepository $offreRepository
    )
    {
        parent::__construct();
        $this->em = $em;
        $this->offreRepository=$offreRepository;

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        //$this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $io->progressStart();
        $items = $this->offreRepository->findAllAndWebsite();

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
        $io->success('Les offres ont ete mises à jour');

        return 0;
    }

}