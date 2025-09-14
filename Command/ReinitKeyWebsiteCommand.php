<?php

namespace Command;

use App\Entity\Module\Contactation;
use App\Entity\Module\ModuleList;
use App\Repository\BoardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class ReinitKeyWebsiteCommand extends Command
{
    protected static $defaultName = 'app:initkey';
    private EntityManagerInterface $em;
    private BoardRepository $websiteRepo;



    public function __construct(
        EntityManagerInterface $em,
        BoardRepository $websiteRepo

    )
    {
        parent::__construct();
        $this->em = $em;
        $this->websiteRepo = $websiteRepo;

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        //$this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $io->progressStart();
        $items = $this->websiteRepo->findAll();

        foreach ($items as $item) {
            $io->progressAdvance();
            $key = $item->getSlug().sha1(uniqid(mt_rand(), true));
            if (!$item->getCodesite()){
                $item->setCodesite($key);
                $moduleevent = new ModuleList();
                $moduleevent->setClassmodule("module_event");
                $moduleevent->setKeymodule($key);
                $item->addListmodule($moduleevent);
                $this->em->persist($moduleevent);
            }
            if (!$item->getContactation()){
                $modulemail = new ModuleList();
                $modulemail->setClassmodule("module_mail");
                $modulemail->setKeymodule($key);
                $contactation=new Contactation();
                $contactation->setKeymodule($key);
                $item->setContactation($contactation);
                $item->addListmodule($modulemail);
                $this->em->persist($contactation);
                $this->em->persist($modulemail);
                }
                $this->em->persist($item);
                $this->em->flush();
            }
        $io->progressFinish();
        $io->success('Les code site et module mail ont ete mis à jour');

        return 0;
    }

}