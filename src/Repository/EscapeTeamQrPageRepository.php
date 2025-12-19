<?php

namespace App\Repository;

use App\Entity\Games\EscapeTeamQrGroup;
use App\Entity\Games\EscapeTeamQrPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EscapeTeamQrPage>
 */
class EscapeTeamQrPageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EscapeTeamQrPage::class);
    }

    /** @return EscapeTeamQrPage[] */
    public function findForGroup(EscapeTeamQrGroup $group): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.group = :group')
            ->setParameter('group', $group)
            ->orderBy('p.createdAt', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByToken(string $token): ?EscapeTeamQrPage
    {
        return $this->findOneBy(['token' => $token]);
    }
}
