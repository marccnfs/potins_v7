<?php

namespace App\Repository;

use App\Entity\Admin\Orders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Orders>
 *
 * @method Orders|null find($id, $lockMode = null, $lockVersion = null)
 * @method Orders|null findOneBy(array $criteria, array $orderBy = null)
 * @method Orders[]    findAll()
 * @method Orders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrdersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orders::class);
    }

    public function save(Orders $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Orders $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Orders[] Returns an array of Orders objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

   public function findOrderEvent($value): ?Orders
   {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id = :val')
            ->setParameter('val', $value)
            ->leftJoin('o.listproducts', 'lp')
            ->addSelect('lp')
            ->leftJoin('o.numclient', 'num')
            ->addSelect('num')
            ->leftJoin('num.idcustomer', 'cus')
            ->addSelect('cus')
            ->leftJoin('lp.subscription', 'sub')
            ->addSelect('sub')
            ->leftJoin('lp.registered', 'rg')
            ->addSelect('rg')
            ->leftJoin('sub.event', 'ev')
            ->addSelect('ev')
            ->leftJoin('ev.locatemedia', 'lm')
            ->addSelect('lm')
            ->leftJoin('ev.potin', 'pt')
            ->addSelect('pt')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOrderEventByLocateMedia($value): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.listproducts', 'lp')
            ->addSelect('lp')
            ->leftJoin('lp.subscription', 'sub')
            ->addSelect('sub')
            ->leftJoin('lp.registered', 'rg')
            ->addSelect('rg')
            ->leftJoin('sub.event', 'ev')
            ->addSelect('ev')
            ->leftJoin('ev.locatemedia', 'lm')
            ->addSelect('lm')
            ->leftJoin('ev.potin', 'pt')
            ->addSelect('pt')
            ->andWhere('lm.id = :val')
            ->setParameter('val', $value)
            ->andWhere('o.valider = :false')
            ->setParameter('false' , false)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findOrderEventByEventId($value): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.listproducts', 'lp')
            ->addSelect('lp')
            ->leftJoin('lp.subscription', 'sub')
            ->addSelect('sub')
            ->leftJoin('lp.docs', 'dcs')
            ->addSelect('dcs')
            ->leftJoin('o.numclient', 'num')
            ->addSelect('num')
            ->leftJoin('num.idcustomer', 'cus')
            ->addSelect('cus')
            ->leftJoin('cus.profil', 'pf')
            ->addSelect('pf')
            ->leftJoin('lp.registered', 'rg')
            ->addSelect('rg')
            ->leftJoin('sub.event', 'ev')
            ->addSelect('ev')
            ->leftJoin('ev.locatemedia', 'lm')
            ->addSelect('lm')
            ->leftJoin('ev.potin', 'pt')
            ->addSelect('pt')
            ->andWhere('ev.id = :val')
            ->setParameter('val', $value)
            ->andWhere('o.valider = :false')
            ->setParameter('false' , false)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findOrderEventByPotin($id): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.listproducts', 'lp')
            ->addSelect('lp')
            ->leftJoin('lp.subscription', 'sub')
            ->addSelect('sub')
            ->leftJoin('lp.registered', 'rg')
            ->addSelect('rg')
            ->leftJoin('sub.event', 'ev')
            ->addSelect('ev')
            ->leftJoin('ev.locatemedia', 'lm')
            ->addSelect('lm')
            ->leftJoin('ev.potin', 'pt')
            ->addSelect('pt')
            ->andWhere('pt.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findResaCustomer($value): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.numclient = :val')
            ->setParameter('val', $value)
            ->andWhere('o.valider = :false')
            ->setParameter('false' , false)
            ->leftJoin('o.listproducts', 'lp')
            ->addSelect('lp')
            ->leftJoin('lp.product', 'pd')
            ->addSelect('pd')
            ->leftJoin('lp.subscription', 'sub')
            ->addSelect('sub')
            ->leftJoin('lp.registered', 'rg')
            ->addSelect('rg')
            ->leftJoin('sub.event', 'ev')
            ->addSelect('ev')
            ->leftJoin('ev.locatemedia', 'lm')
            ->addSelect('lm')
            ->leftJoin('ev.potin', 'pt')
            ->addSelect('pt')
            ->andWhere('pd.id = :prod')
            ->setParameter('prod' , 26)
            ->getQuery()
            ->getResult()
            ;
    }
}
