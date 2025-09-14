<?php

namespace App\Repository;

use App\Entity\Sector\Gps;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Gps|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gps|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gps[]    findAll()
 * @method Gps[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GpsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gps::class);
    }


    /**
     * @param $value
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findCode($value)
    {
        return $this->createQueryBuilder('g')
            -> andWhere('g.code = :val')
            -> setParameter('val', $value)
            -> getQuery()
            -> getOneOrNullResult()
        ;
    }


    /**
     * @param $lon
     * @param $lat
     * @return mixed
     */
    public function findInBetwen($lon, $lat)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.latloc <= :lat')
            ->setParameter('lat', $lat)
            ->andWhere('g.latloc <= :lon')
            ->setParameter('lon', $lon)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param $center
     * @return mixed
     */
    public function findByCenter($center)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.latloc = :lat')
            ->setParameter('lat', $center['lat'])
            ->andWhere('g.latloc = :lon')
            ->setParameter('lon', $center['lon'])
            ->getQuery()
            ->getResult()
            ;
    }

}
