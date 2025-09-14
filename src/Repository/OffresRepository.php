<?php

namespace App\Repository;

use App\Entity\Marketplace\Offres;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Offres>
 *
 * @method Offres|null find($id, $lockMode = null, $lockVersion = null)
 * @method Offres|null findOneBy(array $criteria, array $orderBy = null)
 * @method Offres[]    findAll()
 * @method Offres[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OffresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offres::class);
    }

    public function save(Offres $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Offres $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOffreKey($key)
    {
        $qb=$this->queryOffre();
        return $qb
            ->andWhere('o.keymodule =:key')
            ->setParameter('key', $key)
            ->andwhere('o.deleted = false')
            ->orderBy('o.createAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOffreByCode($code)
    {
        $qb=$this->queryOffre();
        return $qb
            ->leftJoin('ar. htmlcontent', 'ct')
            ->addSelect('ct')
            ->andWhere('o.code =:key')
            ->setParameter('key', $code)
            ->andwhere('o.deleted = false')
            ->orderBy('o.createAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function ListOffresByKey($key): array|int|string
    {
        $qb=$this->queryOffre();
        return $qb
            -> andWhere('o.keymodule = :key')
            -> setParameter('key', $key)
            -> orderBy('o.createAt', 'DESC')
            -> getQuery()
            -> getArrayResult()
            ;
    }

    public function findlast(){
        $date=new dateTime();
        $end = new \DateTimeImmutable();
        $start=$end->sub(new DateInterval('P150D'));
        $qb=$this->queryOffre();
        return $qb
            -> orderBy('o.createAt', 'ASC')
            -> getQuery()
            -> getResult();

    }

    public function findlastBycity($city){ // todo ici plusieru technique pour les dates
        $date=new dateTime();
        $end = new \DateTimeImmutable();
        $start=$end->sub(new DateInterval('P150D'));
        $qb=$this->queryOffre();
        return $qb
            -> andWhere('loc.id =:city')
            -> setParameter('city', $city)
            //   -> andWhere('o.createAt BETWEEN :start AND :end')
            //    -> setParameter('start', date('Y-m-d', strtotime(' - 300 days')))
            //    -> setParameter('end', $date)
            -> orderBy('o.createAt', 'ASC')
            -> getQuery()
            -> getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneOffre($id){
        $qb=$this->queryOffre();
        return $qb
            -> andWhere('o.id = :id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getOneorNullResult();
    }

    public function queryOffre(): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            -> andWhere('o.deleted = false')
            -> leftJoin('o.media', 'm')
            -> addSelect('m')
            -> leftJoin('m.imagejpg', 'img')
            -> addSelect('img')
            -> leftJoin('o.author', 'ath')
            -> addSelect('ath')
            -> leftJoin('o.transactions', 't')
            -> addSelect('t')
            -> leftJoin('o. parution', 'ap')
            -> addSelect('ap')
            -> leftJoin('o. gppresents', 'gp')
            -> addSelect('gp')
            -> leftJoin('gp. articles', 'ar')
            -> addSelect('ar');
    }

    // reste a voir





    public function queryOffreAndMsg(): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            -> andWhere('o.deleted = false')
            -> leftJoin('o.product', 'p')
            -> addSelect('p')
            -> leftJoin('p.htmlcontent', 'ht')
            -> addSelect('ht')
            -> leftJoin('o.tbmessages', 'tm')
            -> addSelect('tm')
            -> leftJoin('tm.idmessage', 'msg')
            -> leftJoin('msg.msgs', 'msgs')
            -> addSelect('msgs')
            -> leftJoin('o.media', 'm')
            -> addSelect('m')
            -> leftJoin('p.tagueries', 'tg')
            -> addSelect('tg')
            -> leftJoin('o.author', 'ath')
            -> addSelect('ath')
            -> leftJoin('m.imagejpg', 'img')
            -> addSelect('img')
            -> leftJoin('o.localisation', 'loc')
            -> addSelect('loc')
            -> leftJoin('o.transactions', 't')
            -> addSelect('t')
            -> leftJoin('o. parution', 'ap')
            -> addSelect('ap');
    }

    public function queryOffreEdit(): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            -> leftJoin('o.product', 'p')
            -> addSelect('p')
            -> leftJoin('o.media', 'm')
            -> addSelect('m')
            -> leftJoin('p.tagueries', 'tg')
            -> addSelect('tg')
            -> leftJoin('o.author', 'ath')
            -> addSelect('ath')
            -> leftJoin('m.imagejpg', 'img')
            -> addSelect('img')
            -> leftJoin('o.localisation', 'loc')
            -> addSelect('loc')
            -> leftJoin('p.htmlcontent', 'ht')
            -> addSelect('ht');
    }

    /**
     * @throws NonUniqueResultException
     */


    public function findOffresByKeyWithOutId($key, $id){
        $qb=$this->queryOffre();
        return $qb
            -> andWhere('o.keymodule =:key')
            -> setParameter('key', $key)
            -> andwhere('o.id !=:id')
            -> setParameter('id', $id)
            -> orderBy('o.createAt', 'DESC')
            -> getQuery()
            -> getResult();
    }





    public function findAroundlastBycity($locate){
        $date=new dateTime();
        $qb=$this->queryOffre();
        return $qb
            -> andwhere('loc.latloc <= :latend')
            -> andWhere('loc.latloc >= :latstart')
            -> andWhere('loc.lonloc >= :lonstart')
            -> andWhere('loc.lonloc <= :lonend')
            -> setParameter('latend', ($locate->getLatloc()+1))
            -> setParameter('latstart', ($locate->getLatloc()-1))
            -> setParameter('lonstart', ($locate->getLonloc()-0.1))
            -> setParameter('lonend', ($locate->getLonloc()+0.1))
            -> andWhere('p.createAt BETWEEN :start AND :end')
            -> setParameter('start', date('Y-m-d', strtotime(' - 400 days')))
            -> setParameter('end', $date)
            -> orderBy('p.createAt', 'ASC')
            -> getQuery()
            -> getResult();
    }




    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countOffre($key): mixed
    {
        return $this->createQueryBuilder('o')
            -> select('count(o.id)')
            -> where('o.deleted = false')
            -> andWhere('o.keymodule =:key')
            -> setParameter('key', $key)
            -> getQuery()
            -> getSingleScalarResult();
    }

    public function findAllByKey($key){ // todo ici plusieru technique pour les dates
        $date=new dateTime();
        $end = new \DateTimeImmutable();
        $start=$end->sub(new DateInterval('P150D'));
        $qb=$this->queryOffre();
        return $qb
            -> andWhere('o.keymodule =:key')
            -> setParameter('key', $key)
            //   -> andWhere('o.createAt BETWEEN :start AND :end')
            //    -> setParameter('start', date('Y-m-d', strtotime(' - 300 days')))
            //    -> setParameter('end', $date)
            -> orderBy('o.createAt', 'ASC')
            -> getQuery()
            -> getResult();

    }

    public function findlastByKey($key){
        $qb=$this->queryOffre();
        return $qb
            -> andWhere('o.keymodule =:key')
            -> setParameter('key', $key)
            -> orderBy('o.createAt', 'DESC')
            -> setMaxResults(1)
            -> getQuery()
            -> getResult();
    }

    public function findCount($idwebsite){
        return $this->createQueryBuilder('o')
            -> where('o.deleted = false')
            -> getQuery()
            -> getResult();
    }

    /**
     * @param $id
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findPstQ2($id){
        $qb=$this->queryOffreEdit();
        return $qb
            -> andWhere('o.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getOneOrNullResult();
    }
}
