<?php

namespace App\Repository;

use App\Entity\Admin\OrderProducts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderProducts>
 *
 * @method OrderProducts|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderProducts|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderProducts[]    findAll()
 * @method OrderProducts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderProducts::class);
    }

    public function save(OrderProducts $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OrderProducts $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function findOrderProdWithEventAndRegistered($value): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id = :val')
            ->setParameter('val', $value)
            ->leftJoin('o.subscription', 'sub')
            ->addSelect('sub')
            ->leftJoin('o.registered', 'rg')
            ->addSelect('rg')
            ->leftJoin('sub.event', 'ev')
            ->addSelect('ev')
            ->leftJoin('ev.potin', 'pt')
            ->addSelect('pt')
            ->getQuery()
            ->getResult()
        ;
   }

    /**
     * @throws NonUniqueResultException
     */
    public function findEventByOrderProdid($value): ?OrderProducts
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id = :val')
            ->setParameter('val', $value)
            ->leftJoin('o.subscription', 'sub')
            ->addSelect('sub')
            ->leftJoin('sub.event', 'ev')
            ->addSelect('ev')
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

//    public function findOneBySomeField($value): ?OrderProducts
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
