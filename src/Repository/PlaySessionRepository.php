<?php

namespace App\Repository;

use App\Entity\Games\EscapeGame;
use App\Entity\Games\PlaySession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlaySession>
 */
class PlaySessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaySession::class);
    }

    /** Top N scores pour un escape (completed uniquement) */
    public function topForGame(EscapeGame $eg, int $limit = 10): array {
        return $this->createQueryBuilder('p')
            ->andWhere('p.escapeGame = :eg')->setParameter('eg', $eg)
            ->andWhere('p.completed = true')
            ->orderBy('p.score', 'DESC')
            ->addOrderBy('p.durationMs', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    /** Meilleurs joueurs (somme de scores) */
    public function leaderboardGlobal(int $limit = 10): array {
        return $this->createQueryBuilder('p')
            ->select('IDENTITY(p.participant) AS pid, SUM(p.score) AS totalScore, COUNT(p.id) AS games')
            ->andWhere('p.completed = true')
            ->groupBy('pid')
            ->orderBy('totalScore', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getArrayResult();
    }
}
