<?php

namespace App\Repository;

use App\Entity\Games\EscapeGame;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EscapeGame>
 */
class EscapeGameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EscapeGame::class);
    }
    /**
     * Retourne l'ensemble des escape games avec leurs propriétaires.
     *
     * @return EscapeGame[]
     */
    public function findAllForAdministration(?string $boardCode = null): array
    {
        $qb = $this->createQueryBuilder('eg')
            ->leftJoin('eg.owner', 'owner')->addSelect('owner')
            ->leftJoin('eg.participant', 'participant')->addSelect('participant')
            ->leftJoin('eg.workshopSession', 'session')->addSelect('session')
            ->leftJoin('session.event', 'sessionEvent')->addSelect('sessionEvent')
            ->leftJoin('sessionEvent.appointment', 'sessionAppointment')->addSelect('sessionAppointment')
            ->orderBy('eg.created_at', 'DESC')
            ->addOrderBy('eg.id', 'DESC');

        if ($boardCode !== null && $boardCode !== '') {
            $qb->andWhere(
                $qb->expr()->orX(
                    'session.id IS NULL',
                    'session.isMaster = true',
                    'sessionEvent.keymodule = :boardCode'
                )
            )->setParameter('boardCode', $boardCode);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

}
