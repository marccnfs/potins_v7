<?php

namespace App\Service\Games;

use App\Entity\Games\EscapeTeam;
use App\Entity\Games\EscapeTeamMember;
use App\Entity\Games\EscapeTeamRun;
use App\Entity\Games\EscapeTeamSession;
use App\Entity\Games\EscapeTeamQrGroup;
use App\Repository\EscapeTeamRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use RuntimeException;

class EscapeTeamRegistrationService
{
    private const COLOR_PALETTE = [
        '#ff6b6b',
        '#4ecdc4',
        '#ffd166',
        '#cdb4db',
        '#6c5ce7',
        '#00a8e8',
        '#f4a261',
        '#2ec4b6',
        '#e76f51',
        '#ff8fa3',
    ];

    public function __construct(
        private readonly EscapeTeamRepository $teamRepository,
        private readonly EntityManagerInterface $em,
        private readonly EscapeTeamAvatarCatalog $avatarCatalog,
    ) {
    }

    /**
     * @param array<int, array{nickname:string}> $members
     */
    public function registerTeam(EscapeTeamRun $run, string $teamName, string $avatarKey, array $members, ?EscapeTeamQrGroup $qrGroup = null): EscapeTeam
    {
        $this->assertRegistrationOpen($run);
        $normalizedName = $this->normalizeName($teamName);
        $this->assertNameIsAvailable($run, $normalizedName);
        $this->assertTeamLimit($run);
        $this->assertAvatar($avatarKey, true);
        $this->assertTeamAvatarAvailable($run, $avatarKey);
        $this->assertMembers($members);

        $team = (new EscapeTeam())
            ->setRun($run)
            ->setName($normalizedName)
            ->setAvatarKey($avatarKey)
            ->setColor($this->pickColor($run))
            ->setQrGroup($qrGroup);

        foreach ($members as $payload) {
            $team->addMember($this->buildMember($payload['nickname'], $avatarKey));
        }

        $session = (new EscapeTeamSession())
            ->setRun($run)
            ->setTeam($team)
            ->setCurrentStep(1)
            ->setLastActivityAt(new DateTimeImmutable());

        $this->em->persist($team);
        $this->em->persist($session);
        $this->em->flush();

        return $team;
    }

    /**
     * @param array<int, array{nickname:string}> $members
     */
    public function updateTeam(EscapeTeam $team, string $teamName, string $avatarKey, array $members): EscapeTeam
    {
        $run = $team->getRun();
        if (!$run) {
            throw new RuntimeException('Impossible de mettre à jour une équipe détachée d’un run.');
        }

        $this->assertRegistrationOpen($run);
        $normalizedName = $this->normalizeName($teamName);
        $this->assertNameIsAvailable($run, $normalizedName, $team->getId());
        $this->assertAvatar($avatarKey, true);
        $this->assertTeamAvatarAvailable($run, $avatarKey, $team->getId());
        $this->assertMembers($members);

        $team->setName($normalizedName);
        $team->setAvatarKey($avatarKey);

        foreach ($team->getMembers() as $existingMember) {
            $team->removeMember($existingMember);
            $this->em->remove($existingMember);
        }

        foreach ($members as $payload) {
            $team->addMember($this->buildMember($payload['nickname'], $avatarKey));
        }

        $this->em->flush();

        return $team;
    }

    public function deleteTeam(EscapeTeam $team): void
    {
        $run = $team->getRun();
        if (!$run) {
            throw new RuntimeException('Impossible de supprimer une équipe détachée d’un run.');
        }

        $this->assertRegistrationOpen($run);

        $this->em->remove($team);
        $this->em->flush();
    }

    private function assertRegistrationOpen(EscapeTeamRun $run): void
    {
        if (!$run->isRegistrationOpen()) {
            throw new RuntimeException('Les inscriptions sont fermées pour ce run.');
        }

        if ($run->getStartedAt()) {
            throw new RuntimeException('Impossible de modifier les équipes après le lancement du jeu.');
        }
    }

    private function assertTeamLimit(EscapeTeamRun $run): void
    {
        $currentCount = $this->teamRepository->count(['run' => $run]);
        if ($currentCount >= $run->getMaxTeams()) {
            throw new RuntimeException('La capacité maximale d’équipes est atteinte.');
        }
    }

    private function assertNameIsAvailable(EscapeTeamRun $run, string $teamName, ?int $ignoreId = null): void
    {
        $existing = $this->teamRepository->findOneBy([
            'run' => $run,
            'name' => $teamName,
        ]);

        if ($existing && $existing->getId() !== $ignoreId) {
            throw new RuntimeException('Ce nom d’équipe est déjà pris. Merci d’en choisir un autre.');
        }
    }

    /**
     * @param array<int, array{nickname:string}> $members
     */
    private function assertMembers(array $members): void
    {
        if (count($members) === 0) {
            throw new InvalidArgumentException('Au moins un membre doit être renseigné.');
        }

        foreach ($members as $member) {
            $nickname = $this->normalizeName($member['nickname'] ?? '');

            if ($nickname === '') {
                throw new InvalidArgumentException('Chaque membre doit avoir un pseudo.');
            }

        }
    }

    private function assertAvatar(string $avatarKey, bool $team): void
    {
        $isValid = $team
            ? $this->avatarCatalog->isValidTeamAvatar($avatarKey)
            : $this->avatarCatalog->isValidMemberAvatar($avatarKey);

        if (!$isValid) {
            throw new InvalidArgumentException('Avatar non autorisé.');
        }
    }

    private function assertTeamAvatarAvailable(EscapeTeamRun $run, string $avatarKey, ?int $ignoreId = null): void
    {
        $existing = $this->teamRepository->findOneBy([
            'run' => $run,
            'avatarKey' => $avatarKey,
        ]);

        if ($existing && $existing->getId() !== $ignoreId) {
            throw new InvalidArgumentException('Cet avatar est déjà pris par une autre équipe.');
        }
    }

    private function buildMember(string $nickname, string $avatarKey): EscapeTeamMember
    {
        $member = new EscapeTeamMember();
        $member->setNickname($this->normalizeName($nickname));
        $member->setAvatarKey($avatarKey);

        return $member;
    }

    private function normalizeName(string $name): string
    {
        return trim(preg_replace('/\s+/', ' ', $name));
    }

    private function pickColor(EscapeTeamRun $run): string
    {
        $index = $this->teamRepository->count(['run' => $run]);
        $paletteSize = max(1, count(self::COLOR_PALETTE));

        return self::COLOR_PALETTE[$index % $paletteSize];
    }
}
