<?php

namespace App\Repository;

use App\Entity\Users\Commentrdv;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<\App\Entity\Users\Commentrdv>
 *
 * @method Commentrdv|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commentrdv|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commentrdv[]    findAll()
 * @method Commentrdv[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentrdvRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentrdv::class);
    }

//    /**
//     * @return Commentrdv[] Returns an array of Commentrdv objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Commentrdv
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
