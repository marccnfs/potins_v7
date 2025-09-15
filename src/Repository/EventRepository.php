<?php

namespace App\Repository;

use App\Entity\Agenda\Event;
use App\Enum\EventCategory;
use App\Enum\EventStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return Event[]
     */
    public function findPublishedInRange(\DateTimeImmutable $fromUtc, \DateTimeImmutable $toUtc, ?string $cat, ?string $commune): array
    {
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.published = true')
            ->andWhere('e.status = :st')->setParameter('st', EventStatus::SCHEDULED)
            ->andWhere('e.startsAt <= :to')->setParameter('to', $toUtc)
            ->andWhere('e.endsAt >= :from')->setParameter('from', $fromUtc)
            ->orderBy('e.startsAt', 'ASC');

        if ($cat) {
            if ($catEnum = EventCategory::tryFrom($cat)) {
                $qb->andWhere('e.category = :cat')->setParameter('cat', $catEnum);
            }
        }

        if ($commune) {
            $qb->andWhere('e.communeCode = :cc')->setParameter('cc', $commune);
        }

        return $qb->getQuery()->getResult();
    }
}
