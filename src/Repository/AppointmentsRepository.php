<?php

namespace App\Repository;

use App\Entity\Agenda\Appointments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Appointments>
 *
 * @method Appointments|null find($id, $lockMode = null, $lockVersion = null)
 * @method Appointments|null findOneBy(array $criteria, array $orderBy = null)
 * @method Appointments[]    findAll()
 * @method Appointments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointments::class);
    }

    public function save(Appointments $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Appointments $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Appointments[] Returns an array of Appointments objects
     */
    public function findAppointmentsBetweenAll(\DateTimeInterface $start, \DateTimeInterface $end, $department):array
    {

        /*???? $qb ?    */  $qb=$this->createQueryBuilder('a')
        ->where('a.start BETWEEN :start AND :end');

        $qb->setParameter('start', $start->format('Y-m-d 00:00:00'))
            ->setParameter('end', $end->format('Y-m-d 23:59:59'))

            ->orderBy('a.start', 'ASC');

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Appointments[] Returns an array of Appointments objects
     */
    public function findAppointwithPeriod($id)
    {

        $qb=$this->createQueryBuilder('a')

            -> andWhere('a.id = :id')
            -> setParameter('id', $id);

        $qb ->leftjoin('a.idPeriods', 'per')
            ->addSelect('per')
            ->orderBy('per.startPeriod', 'ASC');

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Appointments[] Returns an array of Appointments objects
     */
    public function findAppointmentsByDepartment(\DateTimeInterface $start, \DateTimeInterface $end, $department):array
    {

        $qb=$this->createQueryBuilder('a')
            ->where('a.start BETWEEN :start AND :end');

        $qb->setParameter('start', $start->format('Y-m-d 00:00:00'))
            ->setParameter('end', $end->format('Y-m-d 23:59:59'));

        $qb ->leftjoin('a.idTypeAppointment', 'typ', 'WITH','typ.name = :department')
            ->setParameter('department', $department)
            ->addSelect('typ')
            ->orderBy('a.start', 'ASC');

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Appointments avec periods [] Returns an array of Appointments objects
     */
    public function findPeriodAppointsByDepartment(\DateTimeInterface $start, \DateTimeInterface $end, $department):array
    {

        $qb=$this->createQueryBuilder('a')
            ->where('a.start BETWEEN :start AND :end');

        $qb->setParameter('start', $start->format('Y-m-d 00:00:00'))
            ->setParameter('end', $end->format('Y-m-d 23:59:59'));

        $qb ->leftjoin('a.idTypeAppointment', 'typ', 'WITH','typ.name = :department')
            ->setParameter('department', $department)
            ->addSelect('typ');

        $qb ->leftjoin('a.idPeriods', 'p')
            ->addSelect('p');

        $qb ->orderBy('a.start', 'ASC');

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }


    public function findAppointmentsBetweenRole(\DateTimeInterface $start, \DateTimeInterface $end, $role):array
    {
        return $this->createQueryBuilder('a')
            ->where('a.start BETWEEN :start AND :end')

            ->setParameter('start', $start->format('Y-m-d 00:00:00'))
            ->setParameter('end', $end->format('Y-m-d 23:59:59'))
            ->setParameter('role', $role)
            ->orderBy('a.start', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
