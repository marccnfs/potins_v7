<?php

namespace App\Repository;

use App\Entity\Games\EscapeTeamRun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EscapeTeamRun>
 */
class EscapeTeamRunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EscapeTeamRun::class);
    }

    public function findOneByShareSlug(string $slug): ?EscapeTeamRun
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.shareSlug = :slug')
            ->setParameter('slug', $slug)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
