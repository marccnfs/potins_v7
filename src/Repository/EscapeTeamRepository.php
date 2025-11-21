<?php

namespace App\Repository;

use App\Entity\Games\EscapeTeam;
use App\Entity\Games\EscapeTeamRun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EscapeTeam>
 */
class EscapeTeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EscapeTeam::class);
    }

    /** @return EscapeTeam[] */
    public function findForRunOrdered(EscapeTeamRun $run): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.run = :run')
            ->setParameter('run', $run)
            ->orderBy('t.createdAt', 'ASC')
            ->addOrderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
