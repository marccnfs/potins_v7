<?php

namespace App\Service\Games;

use App\Entity\Games\EscapeGame;
use App\Entity\Games\EscapeTeamRun;
use App\Entity\Games\EscapeTeamSession;
use App\Entity\Users\Participant;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class EscapeTeamRunAdminService
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function prepareRun(
        EscapeGame $escapeGame,
        ?Participant $owner,
        string $title,
        ?string $heroImageUrl,
        int $maxTeams = 10,
        ?int $timeLimitSeconds = null,
        array $puzzleConfig = [],
    ): EscapeTeamRun {
        $run = (new EscapeTeamRun())
            ->setEscapeGame($escapeGame)
            ->setOwner($owner)
            ->setTitle(trim($title) !== '' ? trim($title) : ($escapeGame->getTitle() ?? 'Escape par équipes'))
            ->setHeroImageUrl($heroImageUrl)
            ->setMaxTeams($maxTeams)
            ->setTimeLimitSeconds($timeLimitSeconds)
            ->setPuzzleConfig($puzzleConfig)
            ->setStatus(EscapeTeamRun::STATUS_DRAFT);

        $run->ensureShareSlug(fn (string $seed): string => $this->slugify($seed));

        $this->em->persist($run);
        $this->em->flush();

        return $run;
    }

    public function openRegistration(EscapeTeamRun $run): EscapeTeamRun
    {
        if ($run->getStatus() === EscapeTeamRun::STATUS_RUNNING) {
            throw new RuntimeException('Le jeu est déjà lancé, impossible de rouvrir les inscriptions.');
        }

        $now = new DateTimeImmutable();

        $run->ensureShareSlug(fn (string $seed): string => $this->slugify($seed));
        $run->setStatus(EscapeTeamRun::STATUS_REGISTRATION);
        $run->setRegistrationOpenedAt($run->getRegistrationOpenedAt() ?? $now);
        $run->setUpdatedAt($now);
        $run->setEndedAt(null);

        $this->em->flush();

        return $run;
    }

    public function closeRegistration(EscapeTeamRun $run): EscapeTeamRun
    {
        if ($run->getStatus() === EscapeTeamRun::STATUS_RUNNING) {
            throw new RuntimeException('Impossible de fermer les inscriptions après le lancement du jeu.');
        }

        $now = new DateTimeImmutable();

        $run->setStatus(EscapeTeamRun::STATUS_LOCKED);
        $run->setUpdatedAt($now);

        $this->em->flush();

        return $run;
    }


    public function launch(EscapeTeamRun $run, ?int $timeLimitSeconds = null): EscapeTeamRun
    {
        if ($run->getTeams()->count() === 0) {
            throw new RuntimeException('Au moins une équipe doit être inscrite avant de lancer le jeu.');
        }

        $now = new DateTimeImmutable();

        if ($timeLimitSeconds !== null) {
            $run->setTimeLimitSeconds($timeLimitSeconds);
        }

        $run->setStatus(EscapeTeamRun::STATUS_RUNNING);
        $run->setStartedAt($now);
        $run->setEndedAt(null);
        $run->setUpdatedAt($now);

        foreach ($run->getTeams() as $team) {
            $session = $team->getSession();

            if ($session === null) {
                $session = (new EscapeTeamSession())
                    ->setRun($run)
                    ->setTeam($team)
                    ->setCurrentStep(1);
                $this->em->persist($session);
            }

            if ($session->getStartedAt() === null) {
                $session->setStartedAt($now);
            }

            $session->setLastActivityAt($now);

            if ($session->getCurrentStep() === null) {
                $session->setCurrentStep(1);
            }
        }

        $this->em->flush();

        return $run;
    }

    /**
     * Données prêtes à afficher sur la page d'accueil projetée (titre, image, lien d'inscription, compteur d'équipes, statut).
     *
     * @return array{
     *     title: string|null,
     *     heroImageUrl: string|null,
     *     shareSlug: string|null,
     *     status: string,
     *     registrationOpenedAt: ?DateTimeImmutable,
     *     maxTeams: int,
     *     teamCount: int,
     *     timeLimitSeconds: ?int,
     * }
     */
    public function buildLandingContext(EscapeTeamRun $run): array
    {
        return [
            'title' => $run->getTitle(),
            'heroImageUrl' => $run->getHeroImageUrl(),
            'shareSlug' => $run->getShareSlug(),
            'status' => $run->getStatus(),
            'registrationOpenedAt' => $run->getRegistrationOpenedAt(),
            'maxTeams' => $run->getMaxTeams(),
            'teamCount' => $run->getTeams()->count(),
            'timeLimitSeconds' => $run->getTimeLimitSeconds(),
        ];
    }

    private function slugify(string $seed): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $seed) ?? '', '-'));

        return $slug !== '' ? $slug : 'escape-team-run';
    }
}
