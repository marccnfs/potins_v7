<?php

namespace Command;

use App\Repository\TballmessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class MajtaballdateCommand extends Command
{
    protected static $defaultName = 'app:majdatetabmsg';
    private EntityManagerInterface $em;
    private TballmessageRepository $tballmessageRepo;



    public function __construct(
        EntityManagerInterface $em,
        TballmessageRepository $tballmessageRepo
    )
    {
        parent::__construct();
        $this->em = $em;
        $this->tballmessageRepo=$tballmessageRepo;

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        //$this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $io->progressStart();
        $items = $this->tballmessageRepo->findallforcommand();

        foreach ($items as $item) {
            $io->progressAdvance();

            if($item->getLastconvers()==null) {
                if ($item->getTballmsgp() != null) {
                    $item->setLastconvers($item->getTballmsgp()->getCreateAt());
                } elseif ($item->getTballmsgs() != null) {
                    $item->setLastconvers($item->getTballmsgs()->getCreateAt());
                } elseif ($item->getTballmsgd() != null) {
                    $item->setLastconvers($item->getTballmsgd()->getCreateAt());
                }
            }

            $this->em->persist($item);
            $this->em->flush();
            }
        $io->progressFinish();
        $io->success('La table des messages a ete mise à jour');

        return 0;
    }

}