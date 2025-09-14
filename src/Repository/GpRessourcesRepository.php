<?php

namespace App\Repository;

use App\Entity\Module\GpRessources;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GpRessources>
 *
 * @method GpRessources|null find($id, $lockMode = null, $lockVersion = null)
 * @method GpRessources|null findOneBy(array $criteria, array $orderBy = null)
 * @method GpRessources[]    findAll()
 * @method GpRessources[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GpRessourcesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GpRessources::class);
    }

    public function save(GpRessources $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GpRessources $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /*------------------------------query ----------------------------------*/


    public function queryGpRessource(): QueryBuilder
    {
        return $this->createQueryBuilder('f')
            -> andwhere('f.deleted = false')
            -> leftJoin('f.articles', 'a')
            -> addSelect('a')
            -> leftJoin('a.pict', 'p')
            -> addSelect('p')
            -> leftJoin('a.categorie', 'ct')
            -> addSelect('ct');
    }


    //-------------------------------function //

    /**
     * @throws NonUniqueResultException
     */
    public function findById($id){
        $qb=$this->queryGpRessource();
        return $qb
            -> andWhere('f.id =:key')
            -> setParameter('key', $id)
            -> orderBy('f.createAt', 'ASC')
            -> getQuery()
            -> getOneOrNullResult();
    }

    public function findByKey($key){
        $qb=$this->queryGpRessource();
        return $qb
            -> andWhere('f.keymodule =:key')
            -> setParameter('key', $key)
            -> orderBy('f.createAt', 'ASC')
            -> getQuery()
            -> getResult();
    }


    public function findlastformuleKey($key){
        $qb=$this->queryGpRessource();
        return $qb
            -> andWhere('f.keymodule =:key')
            -> setParameter('key', $key)
            -> orderBy('f.createAt', 'DESC')
            -> getQuery()
            -> getResult();
    }

    public function findformuleKey($key){
        $qb=$this->queryGpRessource();
        return $qb
            -> andWhere('f.keymodule =:key')
            -> setParameter('key', $key)
            -> orderBy('f.createAt', 'ASC')
            -> getQuery()
            -> getResult();
    }

    public function findFormulessByKeyWithOutId($key,$id){
        $qb=$this->queryGpRessource();
        return $qb
            -> andWhere('f.keymodule =:key')
            -> setParameter('key', $key)
            -> andwhere('f.id !=:id')
            -> setParameter('id', $id)
            -> orderBy('f.createAt', 'DESC')
            -> getQuery()
            -> getResult();
    }


    public function findFormuleById($id){
        $qb=$this->queryGpRessource();
        return $qb
            -> andWhere('f.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getOneOrNullResult();
    }
}
