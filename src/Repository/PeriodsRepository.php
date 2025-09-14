<?php

namespace App\Repository;

use App\Entity\Agenda\Periods;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Periods>
 *
 * @method Periods|null find($id, $lockMode = null, $lockVersion = null)
 * @method Periods|null findOneBy(array $criteria, array $orderBy = null)
 * @method Periods[]    findAll()
 * @method Periods[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeriodsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Periods::class);
    }

    public function save(Periods $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Periods $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Periods avec periods [] Returns an array of Appointments objects
     */
    public function findByPeriod(\DateTimeInterface $start, \DateTimeInterface $end):array
    {

        $qb=$this->createQueryBuilder('p')
            ->where('p.startPeriod <= :end')
            ->andWhere('p.endPeriod >= :start');

        $qb->setParameter('start', $start->format('Y-m-d 00:00:00'))
            ->setParameter('end', $end->format('Y-m-d 23:59:59'));

        $qb ->leftjoin('p.idAppointment', 'a')
            ->addSelect('a');

        $qb ->orderBy('p.startPeriod', 'ASC');

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Periods avec periods [] Returns an array of Appointments objects
     */
    public function findRdvByPeriod(\DateTimeInterface $start, \DateTimeInterface $end):array
    {

        $qb=$this->createQueryBuilder('p')
            ->where('p.startPeriod <= :end')
            ->andWhere('p.endPeriod >= :start');

        $qb->setParameter('start', $start->format('Y-m-d 00:00:00'))
            ->setParameter('end', $end->format('Y-m-d 23:59:59'));

        $qb ->leftjoin('p.idAppointment', 'a')
            ->addSelect('a');

        $qb ->andWhere('a.idTypeAppointment = :rdv')
            ->setParameter('rdv', '3' );

        $qb ->orderBy('p.startPeriod', 'ASC');

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }
}
