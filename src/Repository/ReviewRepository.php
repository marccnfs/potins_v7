<?php

namespace App\Repository;

use App\Entity\Ressources\Reviews;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reviews>
 *
 * @method Reviews|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reviews|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reviews[]    findAll()
 * @method Reviews[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reviews::class);
    }
    public function queryReviewAll(): QueryBuilder
    {
        return $this->createQueryBuilder('r')
            -> andwhere('r.deleted = false')
            -> leftJoin('r.pict', 'pi')
            -> addSelect('pi')
            -> leftJoin('r.fiche', 'f')
            -> addSelect('f');
    }

    public function findAllById($id){
        $qb=$this->queryReviewAll();
        return $qb
            -> andWhere('p.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findForEdit($id){
        $qb=$this->queryReviewAll();
        return $qb
            -> andWhere('r.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getOneOrNullResult();
    }


    public function findByName($q){
        $qb=$this->queryReviewAll();
        return $qb
            -> andWhere('r.titre  LIKE :key')
            -> setParameter('key', '%'.$q.'%')
            -> getQuery()
            -> getResult();
    }

}
