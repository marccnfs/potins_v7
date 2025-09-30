<?php

namespace App\Repository;

use App\Entity\Games\EscapeGame;
use App\Entity\Games\PlaySession;
use App\Entity\Users\Participant;
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
    public function findLatestActiveForParticipant(EscapeGame $eg, Participant $participant): ?PlaySession
    {
        return $this->createQueryBuilder('ps')
            ->andWhere('ps.escapeGame = :eg')->setParameter('eg', $eg)
            ->andWhere('ps.participant = :participant')->setParameter('participant', $participant)
            ->andWhere('ps.completed = false')
            ->orderBy('ps.updatedAt', 'DESC')
            ->addOrderBy('ps.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    /** @return PlaySession[] */
    public function findRecentForParticipant(EscapeGame $eg, Participant $participant, int $limit = 5): array
    {
        return $this->createQueryBuilder('ps')
            ->andWhere('ps.escapeGame = :eg')->setParameter('eg', $eg)
            ->andWhere('ps.participant = :participant')->setParameter('participant', $participant)
            ->orderBy('ps.createdAt', 'DESC')
            ->addOrderBy('ps.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    /** @return PlaySession[] */
    public function findAllForParticipant(Participant $participant): array
    {
        return $this->createQueryBuilder('ps')
            ->addSelect('eg')
            ->innerJoin('ps.escapeGame', 'eg')
            ->andWhere('ps.participant = :participant')->setParameter('participant', $participant)
            ->orderBy('ps.createdAt', 'DESC')
            ->addOrderBy('ps.id', 'DESC')
            ->getQuery()->getResult();
    }
}
