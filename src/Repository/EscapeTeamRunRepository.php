<?php

namespace App\Repository;

use App\Entity\Games\EscapeTeamRun;
use App\Entity\Users\Participant;
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

    public function findOneByRegistrationCode(string $code): ?EscapeTeamRun
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.registrationCode = :code')
            ->setParameter('code', strtoupper($code))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<int, EscapeTeamRun>
     */
    public function findAllForOwner(Participant $owner): array
    {
        return $this->createQueryBuilder('run')
            ->andWhere('run.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('run.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
