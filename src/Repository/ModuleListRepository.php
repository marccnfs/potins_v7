<?php

namespace App\Repository;

use App\Entity\Module\ModuleList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ModuleList>
 *
 * @method ModuleList|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModuleList|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModuleList[]    findAll()
 * @method ModuleList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModuleListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModuleList::class);
    }

    public function save(ModuleList $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ModuleList $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findwebsiteBykeymodule($key)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.keymodule = :val')
            ->setParameter('val', $key)
            -> leftJoin('m.module', 'md')
            -> addSelect('md')
            -> leftJoin('md.website', 'w')
            -> addSelect('w')
            -> leftJoin('w.locality', 'locwb')
            -> addSelect('locwb')
            -> leftJoin('w.template', 'tp')
            -> addSelect('tp')
            -> leftJoin('tp.logo', 'lg')
            -> addSelect('lg')
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param $key
     * @return int|mixed|string|null
     * @throws NonUniqueResultException
     */
    public function findTheWebsiteBykeymodule($key)
    {
        return $this->createQueryBuilder('m')
            -> andWhere('m.keymodule = :val')
            -> setParameter('val', $key)
            -> leftJoin('m.module', 'md')
            -> addSelect('md')
            -> leftJoin('md.website', 'w')
            -> addSelect('w')
            -> leftJoin('w.locality', 'locwb')
            -> addSelect('locwb')
            -> leftJoin('w.template', 'tp')
            -> addSelect('tp')
            -> leftJoin('tp.logo', 'lg')
            -> addSelect('lg')
            -> getQuery()
            -> getOneOrNullResult()
            ;
    }

    public function findListforWebsite($id){

        return $this->createQueryBuilder('m')
            -> andWhere('m.module = :val')
            -> setParameter('val', $id)
            -> getQuery()
            -> getResult()
            ;
    }
}
