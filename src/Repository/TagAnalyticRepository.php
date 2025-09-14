<?php

namespace App\Repository;

use App\Entity\HyperCom\TagAnalytic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TagAnalytic>
 *
 * @method TagAnalytic|null find($id, $lockMode = null, $lockVersion = null)
 * @method TagAnalytic|null findOneBy(array $criteria, array $orderBy = null)
 * @method TagAnalytic[]    findAll()
 * @method TagAnalytic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagAnalyticRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TagAnalytic::class);
    }

    public function save(TagAnalytic $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TagAnalytic $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return TagAnalytic[] Returns an array of TagAnalytic objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TagAnalytic
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
