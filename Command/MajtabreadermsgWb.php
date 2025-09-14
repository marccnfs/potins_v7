<?php

namespace Command;


use App\Entity\LogMessages\Tbmsgs;
use App\Repository\MsgWebisteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class MajtabreadermsgWb extends Command
{
    protected static $defaultName = 'app:majtabreader';
    private EntityManagerInterface $em;
    private MsgWebisteRepository $msgRepository;



    public function __construct(
        EntityManagerInterface $em,
        MsgWebisteRepository $msgRepository
    )
    {
        parent::__construct();
        $this->em = $em;
        $this->msgRepository=$msgRepository;

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        //$this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $io->progressStart();
        $items = $this->msgRepository->findAllAndTbreader();

        foreach ($items as $item) {
            $io->progressAdvance();
            $tabDispatch=[];
            $website=$item->getWebsitedest();
            $pws=$website->getSpwsites();
            foreach ($pws as $pw ){
                if($pw->getRole()=="admin" || $pw->isSuper()){
                    $tabDispatch[]=$pw->getDisptachwebsite();
                }
            }

            foreach ($item->getMsgs() as $msg) {
                foreach ($tabDispatch as $dispatch) {
                    $reader = new Tbmsgs();
                    $msg->addTabreader($reader);
                    $reader->setIdispatch($dispatch->getId());
                    $reader->setIsRead(true);
                    $this->em->persist($msg);
                    $this->em->flush();
                }
            }
        }

        $io->progressFinish();
        $io->success('Les tables reader ont été mises à jour');

        return 0;
    }

}