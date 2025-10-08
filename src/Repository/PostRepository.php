<?php

namespace App\Repository;

use App\Entity\Posts\Post;
use DateTime;
use DateInterval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function save(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllPost()
    {
        $qb=$this->queryPostAll();
        return $qb
            -> orderBy('p.createAt', 'DESC')
            -> getQuery()
            -> getResult();
    }

    public function findAllPotinsActivWithOutPotinsId( $id){
        $qb=$this->queryPostAll();
        return $qb
            -> andwhere('p.id !=:id')
            -> setParameter('id', $id)
            -> orderBy('p.createAt', 'DESC')
            -> getQuery()
            -> getResult();
    }


    /**
     * @throws NonUniqueResultException
     */
    public function findOnePostById($id){
        $qb=$this->queryPostAll();
        return $qb
            -> andWhere('p.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getOneorNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findPostAndGpById($id){
        $qb=$this->queryPostAllReviews();
        return $qb
            -> andWhere('p.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getOneorNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOnePostAndReviews($id){
        $qb=$this->queryPostAllReviews();
        return $qb
            -> andWhere('p.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getOneorNullResult();
    }

    public function queryPostAllReviews(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            -> andwhere('p.deleted = false')
            -> leftJoin('p.tagueries', 'tg')
            -> addSelect('tg')
            -> leftJoin('p.author', 'aut')
            -> addSelect('aut')
            -> leftJoin('p.media', 'md')
            -> addSelect('md')
            -> leftJoin('p.gpreview', 'gp')
            -> addSelect('gp')
            -> leftJoin('gp.media', 'mdg')
            -> addSelect('mdg')
            -> leftJoin('mdg.imagejpg', 'jpgpi')
            -> addSelect('jpgpi')
            -> leftJoin('gp.reviews', 'rws')
            -> addSelect('rws')
            -> leftJoin('rws.pict', 'pi')
            -> addSelect('pi')
            -> leftJoin('p.gpressources', 'gprs')
            -> addSelect('gprs')
            -> leftJoin('gprs.articles', 'rs')
            -> addSelect('rs')


            ;
    }

    public function queryPostAll(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            -> andwhere('p.deleted = false')
            -> leftJoin('p.tagueries', 'tg')
            -> addSelect('tg')
            -> leftJoin('p.author', 'aut')
            -> addSelect('aut')
            -> leftJoin('p.media', 'md')
            -> addSelect('md')
            -> leftJoin('md.imagejpg', 'jpg')
            -> addSelect('jpg')
            -> leftJoin('p.htmlcontent', 'ht')
            -> addSelect('ht')
            -> leftJoin('p.localisation', 'loc')
            -> addSelect('loc');
    }



    public function queryPostReview(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            -> andwhere('p.deleted = false')
            -> leftJoin('p.author', 'aut')
            -> addSelect('aut')
            -> leftJoin('p.media', 'md')
            -> addSelect('md')
            -> leftJoin('md.imagejpg', 'jpg')
            -> addSelect('jpg')
            -> leftJoin('p.gpreview', 'gp')
            -> addSelect('gp')
            -> leftJoin('gp.reviews', 'rw')
            -> addSelect('rw')
            -> leftJoin('p.gpressources', 'gprs')
            -> addSelect('gprs')
            -> leftJoin('gprs.articles', 'rs')
            -> addSelect('rs')
            -> leftJoin('gp.media', 'mgp')
            -> addSelect('mgp')
            -> leftJoin('p.events', 'evs')
            -> addSelect('evs')
            -> leftJoin('evs.docs', 'dcs')
            -> addSelect('dcs')
            -> leftJoin('dcs.pict', 'pidc')
            -> addSelect('pidc')
            -> leftJoin('rw.pict', 'pi')
            -> addSelect('pi')
            -> leftJoin('mgp.imagejpg', 'mgpjpg')
            -> addSelect('mgpjpg')
           ;
    }

    public function findlast(){ // todo ici plusieru technique pour les dates
        $date=new dateTime();
        $end = new \DateTimeImmutable();
        $start=$end->sub(new DateInterval('P150D'));
        $qb=$this->queryPostAll();
        return $qb
            -> andWhere('p.createAt BETWEEN :start AND :end')
            -> setParameter('start', date('Y-m-d', strtotime(' - 300 days')))
            -> setParameter('end', $date)
            -> orderBy('p.createAt', 'ASC')
            -> getQuery()
            -> getResult();

    }

    public function findlastBycity($city){ // todo ici plusieru technique pour les dates
        $date=new dateTime();
        $end = new \DateTimeImmutable();
        $start=$end->sub(new DateInterval('P150D'));
        $qb=$this->queryPostAll();
        return $qb
            -> andWhere('loc.id =:city')
            -> setParameter('city', $city)
            -> andWhere('p.createAt BETWEEN :start AND :end')
            //-> andWhere('loc.slugcity =:city')
            //-> setParameter('city', $city)
            //-> setParameter('start', $start->format('Y-m-d 00:00:00'))
            //-> setParameter('end', $end->format('Y-m-d 23:59:59'))
            -> setParameter('start', date('Y-m-d', strtotime(' - 300 days')))
            -> setParameter('end', $date)
            //-> setParameter('start', $start) ça marche aussi
            //-> setParameter('end', $end)  comme avec strotime à verifier
            -> orderBy('p.createAt', 'ASC')
            -> getQuery()
            -> getResult();

    }

    public function findPstKey($key){
        $qb=$this->queryPostAll();
        return $qb
            ->andWhere('p.keymodule = :key')
            ->setParameter('key', $key)
            ->andWhere('p.deleted = false')
            ->orderBy('p.createAt', 'DESC')
            ->getQuery()
            ->getResult();
    }


    /**
     * @throws NonUniqueResultException
     */
    public function findOnePostAndMsg($id){
        $qb=$this->queryPostAllandMsg();
        return $qb
            -> andWhere('p.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getOneorNullResult();
    }



    public function queryPostAllandMsg(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            -> andwhere('p.deleted = false')
            -> leftJoin('p.tagueries', 'tg')
            -> addSelect('tg')
            -> leftJoin('p.author', 'aut')
            -> addSelect('aut')
            -> leftJoin('p.media', 'md')
            -> addSelect('md')
            -> leftJoin('md.imagejpg', 'jpg')
            -> addSelect('jpg')
            -> leftJoin('p.htmlcontent', 'ht')
            -> addSelect('ht')
            -> leftJoin('p.localisation', 'loc')
            -> leftJoin('p.tbmessages', 'tb')
            -> addSelect('tb')
            -> leftJoin('tb.post', 'po')
            -> addSelect('po')
            -> leftJoin('tb.offre', 'ofr')
            -> addSelect('ofr')
            -> leftJoin('tb.idmessage', 'msgp')
            -> addSelect('msgp')
            -> leftJoin('msgp.msgs', 'msgs')
            -> addSelect('msgs')
            -> leftJoin('msgs.tabreaders', 'tr')
            -> addSelect('tr')
            -> leftJoin('tr.tabnotifs', 'nt')
            -> addSelect('nt');
    }






    // reste à voir .....



    public function queryPost(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            -> andwhere('p.deleted = false')
            -> leftJoin('p.tagueries', 'tg')
            -> addSelect('tg')
            -> leftJoin('p.author', 'aut')
            -> addSelect('aut')
            -> leftJoin('p.media', 'md')
            -> addSelect('md')
            -> leftJoin('md.imagejpg', 'jpg')
            -> addSelect('jpg');
    }




    /**
     * @throws NonUniqueResultException
     */
    public function findPstQ0($id){
        $qb=$this->queryPostAll();
        return $qb
            -> andWhere('p.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getOneOrNullResult();
    }

    public function findPostsByKeyWithOutId($key, $id){
        $qb=$this->queryPostAll();
        return $qb
            -> andWhere('p.keymodule =:key')
            -> setParameter('key', $key)
            -> andwhere('p.id !=:id')
            -> setParameter('id', $id)
            -> orderBy('p.createAt', 'DESC')
            -> getQuery()
            -> getResult();
    }




    public function ListpostByKey($key){
        $qb=$this->queryPostAll();
        return $qb
            -> andWhere('p.keymodule =:key')
            -> setParameter('key', $key)
            -> orderBy('p.createAt', 'DESC')
            -> getQuery()
            -> getResult();
    }



    public function findlastByKey($key){
        $qb=$this->queryPostAll();
        return $qb
            -> andWhere('p.keymodule =:key')
            -> setParameter('key', $key)
            -> orderBy('p.createAt', 'DESC')
            -> setMaxResults(1)
            -> getQuery()
            -> getResult();
    }




    public function findAllByKey($key){
        $date=new dateTime();
        $end = new \DateTimeImmutable();
        $start=$end->sub(new DateInterval('P150D'));
        $qb=$this->queryPostAll();
        return $qb
            -> andWhere('p.keymodule =:key')
            -> setParameter('key', $key)
            -> andWhere('p.createAt BETWEEN :start AND :end')
            //-> andWhere('loc.slugcity =:city')
            //-> setParameter('city', $city)
            //-> setParameter('start', $start->format('Y-m-d 00:00:00'))
            //-> setParameter('end', $end->format('Y-m-d 23:59:59'))
            -> setParameter('start', date('Y-m-d', strtotime(' - 300 days')))
            -> setParameter('end', $date)
            //-> setParameter('start', $start) ça marche aussi
            //-> setParameter('end', $end)  comme avec strotime à verifier
            -> orderBy('p.createAt', 'ASC')
            -> getQuery()
            -> getResult();

    }

    public function findAroundlastBycity($locate){
        $date=new dateTime();
        $qb=$this->queryPostAll();
        return $qb
            -> leftJoin('p.localisation', 'l')
            -> addSelect('l')
            -> andwhere('l.latloc <= :latend')
            -> andWhere('l.latloc >= :latstart')
            -> andWhere('l.lonloc >= :lonstart')
            -> andWhere('l.lonloc <= :lonend')
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
     */
    public function findOnePost($id){
        $qb=$this->queryPostAllandMsg();
        return $qb
            -> andWhere('p.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getOneorNullResult();
    }

    /**
     * @param $key
     * @return mixed
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countPost($key): mixed
    {
        return $this->createQueryBuilder('p')
            -> select('count(p.id)')
            -> where('p.deleted = false')
            -> andWhere('p.keymodule =:key')
            -> setParameter('key', $key)
            -> getQuery()
            -> getSingleScalarResult();
    }
}
