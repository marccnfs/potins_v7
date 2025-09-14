<?php

namespace App\Repository;

use App\Entity\Quiz\Userquizz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Userquizz>
 *
 * @method Userquizz|null find($id, $lockMode = null, $lockVersion = null)
 * @method Userquizz|null findOneBy(array $criteria, array $orderBy = null)
 * @method Userquizz[]    findAll()
 * @method Userquizz[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserquizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Userquizz::class);
    }

//    /**
//     * @return Userqizz[] Returns an array of Userqizz objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Userqizz
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
