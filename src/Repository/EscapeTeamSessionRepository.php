<?php

namespace App\Repository;

use App\Entity\Games\EscapeTeam;
use App\Entity\Games\EscapeTeamRun;
use App\Entity\Games\EscapeTeamSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EscapeTeamSession>
 */
class EscapeTeamSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EscapeTeamSession::class);
    }

    public function findOneByTeam(EscapeTeam $team): ?EscapeTeamSession
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.team = :team')
            ->setParameter('team', $team)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return EscapeTeamSession[] */
    public function findForRun(EscapeTeamRun $run): array
    {
        return $this->createQueryBuilder('s')
            ->addSelect('t')
            ->join('s.team', 't')
            ->andWhere('s.run = :run')
            ->setParameter('run', $run)
            ->orderBy('t.createdAt', 'ASC')
            ->addOrderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
