<?php

namespace App\Repository;

use App\Entity\Module\GpReview;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GpReview>
 *
 * @method GpReview|null find($id, $lockMode = null, $lockVersion = null)
 * @method GpReview|null findOneBy(array $criteria, array $orderBy = null)
 * @method GpReview[]    findAll()
 * @method GpReview[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GpReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GpReview::class);
    }

    public function save(GpReview $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GpReview $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /*------------------------------query ----------------------------------*/


    public function querygpreview(): QueryBuilder
    {
        return $this->createQueryBuilder('r')
            -> leftJoin('r.author', 'a')
            -> addSelect('a')
            -> leftJoin('r.media', 'm')
            -> addSelect('m')
            -> leftJoin('r.potin', 'p')
            -> addSelect('p')
            -> leftJoin('r.reviews', 'rw')
            -> addSelect('rw');
    }
    public function queryGpreviewAndReview(): QueryBuilder
    {
        return $this->createQueryBuilder('gp')
            -> leftJoin('gp.author', 'a')
            -> addSelect('a')
            -> leftJoin('gp.media', 'm')
            -> addSelect('m')
            -> leftJoin('m.imagejpg', 'i')
            -> addSelect('i')
            -> leftJoin('gp.potin', 'p')
            -> addSelect('p')
            -> leftJoin('gp.reviews', 'rw')
            -> addSelect('rw')
            -> leftJoin('rw.fiche', 'fc')
            -> addSelect('fc')
            -> leftJoin('rw.pict', 'pic')
            -> addSelect('pic');
    }

    public function findGpreviewsByPost($id){
        $qb=$this->querygpreview();
        return $qb
            -> andwhere('p.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findGpreviewsAllByPost($id){
        $qb=$this->queryGpreviewAndReview();
        return $qb
            -> andwhere('p.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findGpreviewsAll($id){
        $qb=$this->queryGpreviewAndReview();
        return $qb
            -> andwhere('gp.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getOneOrNullResult();
    }


    /**
     * @throws NonUniqueResultException
     */
    public function findFormuleById($id){
        $qb=$this->querygpreview();
        return $qb
            -> andWhere('r.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getOneOrNullResult();
    }
}
