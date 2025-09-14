<?php

namespace App\Repository;

use App\Entity\Admin\PreOrderResa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PreOrderResa>
 *
 * @method PreOrderResa|null find($id, $lockMode = null, $lockVersion = null)
 * @method PreOrderResa|null findOneBy(array $criteria, array $orderBy = null)
 * @method PreOrderResa[]    findAll()
 * @method PreOrderResa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PreOrderResaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PreOrderResa::class);
    }

    public function save(PreOrderResa $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PreOrderResa $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function findPreoAndJoin($value)
    {
        return $this->createQueryBuilder('p')
            -> andWhere('p.id = :val')
            -> setParameter('val', $value)
            -> leftJoin('p.event', 'ev')
            -> addSelect('ev')
            -> leftJoin('p.customer', 'cu')
            -> addSelect('cu')
            -> getQuery()
            -> getOneorNullResult();
    }


}
