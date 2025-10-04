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

    public function findPendingByBoardIdWithAssociations(int $boardId): array
    {
        return $this->createQueryBuilder('orderProduct')
            ->leftJoin('orderProduct.subscription', 'subscription')
            ->addSelect('subscription')
            ->leftJoin('subscription.event', 'event')
            ->addSelect('event')
            ->leftJoin('event.locatemedia', 'board')
            ->leftJoin('orderProduct.registered', 'registered')
            ->addSelect('registered')
            ->leftJoin('orderProduct.docs', 'docs')
            ->addSelect('docs')
            ->leftJoin('orderProduct.order', 'orderEntity')
            ->addSelect('orderEntity')
            ->andWhere('board.id = :boardId')
            ->setParameter('boardId', $boardId)
            ->andWhere('orderEntity.valider = :validated')
            ->setParameter('validated', false)
            ->orderBy('event.id', 'ASC')
            ->addOrderBy('subscription.starttime', 'ASC')
            ->addOrderBy('orderProduct.id', 'ASC')
            ->getQuery()
            ->getResult();
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
