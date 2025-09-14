<?php

namespace App\Repository;

use App\Entity\Sector\Sectors;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sectors>
 *
 * @method Sectors|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sectors|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sectors[]    findAll()
 * @method Sectors[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SectorsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sectors::class);
    }

    public function save(Sectors $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sectors $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function findWithAdressByCodesite($value): ?Sectors
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.codesite = :val')
            ->setParameter('val', $value)
            ->leftJoin('s.adresse','a')
            ->addSelect('a')
            ->leftJoin('a.gps','g')
            ->addSelect('g')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByCodesite($value): ?Sectors
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.codesite = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
