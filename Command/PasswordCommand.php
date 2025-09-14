<?php

namespace Command;

use App\Repository\UserRepository;
use App\Util\PasswordUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PasswordCommand extends Command
{
    protected static $defaultName = 'app:remotepsw';
    private EntityManagerInterface $em;
    private UserRepository $userrepo;
    private PasswordUpdater $passwordupdater;


    public function __construct(
        EntityManagerInterface $em,
        NormalizerInterface    $normalizer,
        UserRepository         $userRepository,
        PasswordUpdater        $passwordUpdater
    )
    {
        parent::__construct();
        $this->em = $em;
        $this->userrepo = $userRepository;
        $this->passwordupdater = $passwordUpdater;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        //$this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $io->progressStart();
        $items = $this->userrepo->findAllUsersAndProfil();

        foreach ($items as $item) {
            $io->progressAdvance();
            if ($item->getId() == 1 || $item->getId() == 2) {
                $profil = $item->getUseridentity();
                $password = $profil->getMdpfirst();
                if ($password) {
                    $this->passwordupdater->hashPasswordstring($item, $password);
                } else {
                    $this->passwordupdater->hashPasswordstring($item, 'test');
                }
                $this->em->persist($item);
            }
            //$this->em->clear();
        }
        $this->em->flush();
        $io->progressFinish();
        $io->success('Les mots de passe ont bien été mis a jour');

        return 0;
    }
}
