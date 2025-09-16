<?php

namespace Command;

use App\Repository\UserRepository;
use App\Util\PasswordUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PasswordCommand extends Command
{
    protected static $defaultName = 'app:remotepsw';
    private const MIN_PASSWORD_LENGTH = 12;
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

    protected function configure(): void
    {
        $this
            ->setDescription('Updates the passwords of remote users.')
            ->addArgument(
                'password',
                InputArgument::OPTIONAL,
                'Secure fallback password used when no profile password is provided.'
            )
            ->addOption(
                'generate',
                'g',
                InputOption::VALUE_NONE,
                'Generate a secure random fallback password.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $fallbackPassword = $input->getArgument('password');
        $shouldGeneratePassword = (bool) $input->getOption('generate');

        if ($shouldGeneratePassword && $fallbackPassword) {
            $io->error('Provide either a password argument or the --generate option, not both.');

            return Command::FAILURE;
        }

        if ($shouldGeneratePassword) {
            $fallbackPassword = $this->generateSecurePassword();
            $io->warning('A secure random password has been generated. Store it safely.');
            $io->note(sprintf('Generated password: %s', $fallbackPassword));
        }

        if (!$fallbackPassword) {
            $io->error('A secure password is required. Provide one as an argument or use the --generate option.');

            return Command::FAILURE;
        }

        if (!$this->isPasswordSecure($fallbackPassword)) {
            $io->error(sprintf('The provided password must be at least %d characters long and contain upper-case, lower-case, digit, and special characters.', self::MIN_PASSWORD_LENGTH));

            return Command::FAILURE;
        }
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
                    $this->passwordupdater->hashPasswordstring($item, $fallbackPassword);
                }
                $this->em->persist($item);
            }
            //$this->em->clear();
        }
        $this->em->flush();
        $io->progressFinish();
        $io->success('Les mots de passe ont bien été mis a jour');

        return Command::SUCCESS;
    }

    private function isPasswordSecure(string $password): bool
    {
        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            return false;
        }

        $hasLowercase = (bool) preg_match('/[a-z]/', $password);
        $hasUppercase = (bool) preg_match('/[A-Z]/', $password);
        $hasDigit = (bool) preg_match('/\d/', $password);
        $hasSpecial = (bool) preg_match('/[^\w]/', $password);

        return $hasLowercase && $hasUppercase && $hasDigit && $hasSpecial;
    }

    private function generateSecurePassword(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*()-_=+[]{}<>?';
        $alphabetLength = strlen($alphabet);
        $passwordLength = max(self::MIN_PASSWORD_LENGTH, 20);

        $password = '';
        for ($i = 0; $i < $passwordLength; $i++) {
            $password .= $alphabet[random_int(0, $alphabetLength - 1)];
        }

        return $password;
    }

}
