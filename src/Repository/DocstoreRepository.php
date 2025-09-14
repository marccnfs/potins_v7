<?php

namespace App\Repository;

use App\Entity\Media\Docstore;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Docstore>
 *
 * @method Docstore|null find($id, $lockMode = null, $lockVersion = null)
 * @method Docstore|null findOneBy(array $criteria, array $orderBy = null)
 * @method Docstore[]    findAll()
 * @method Docstore[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocstoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Docstore::class);
    }

//    /**
//     * @return Docstore[] Returns an array of Docstore objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Docstore
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
