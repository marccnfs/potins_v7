<?php

namespace App\Repository;

use App\Entity\Games\EscapeTeamQrGroup;
use App\Entity\Games\EscapeTeamRun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EscapeTeamQrGroup>
 */
class EscapeTeamQrGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EscapeTeamQrGroup::class);
    }

    /** @return EscapeTeamQrGroup[] */
    public function findForRun(EscapeTeamRun $run): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.run = :run')
            ->setParameter('run', $run)
            ->orderBy('g.createdAt', 'ASC')
            ->addOrderBy('g.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
