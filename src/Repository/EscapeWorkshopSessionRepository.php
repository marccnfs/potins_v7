<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Games\EscapeWorkshopSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EscapeWorkshopSession>
 */
final class EscapeWorkshopSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EscapeWorkshopSession::class);
    }

    public function generateUniqueCode(): string
    {
        do {
            $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while ($this->findOneBy(['code' => $code]));

        return $code;
    }

    public function findOneByCode(?string $code): ?EscapeWorkshopSession
    {
        if ($code === null) {
            return null;
        }

        return $this->findOneBy(['code' => strtoupper(trim($code))]);
    }

    public function existsCode(string $code, ?int $ignoreId = null): bool
    {
        $qb = $this->createQueryBuilder('session')
            ->select('COUNT(session.id)')
            ->andWhere('session.code = :code')
            ->setParameter('code', strtoupper(trim($code)));

        if ($ignoreId !== null) {
            $qb->andWhere('session.id <> :id')->setParameter('id', $ignoreId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @return array<int, EscapeWorkshopSession>
     */
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('session')
            ->leftJoin('session.event', 'event')->addSelect('event')
            ->leftJoin('event.appointment', 'appointment')->addSelect('appointment')
            ->leftJoin('event.potin', 'potin')->addSelect('potin')
            ->leftJoin('session.escapeGames', 'game')->addSelect('game')
            ->leftJoin('game.owner', 'owner')->addSelect('owner')
            ->orderBy('session.isMaster', 'DESC')
            ->addOrderBy('event.titre', 'ASC')
            ->addOrderBy('session.label', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
