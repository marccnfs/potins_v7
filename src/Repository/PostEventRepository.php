<?php

namespace App\Repository;

use App\Entity\Module\PostEvent;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PostEvent>
 *
 * @method PostEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostEvent[]    findAll()
 * @method PostEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostEvent::class);
    }

    public function save(PostEvent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PostEvent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findEventByOneLocateMedia($id){
        $date=new dateTime();
        $date->sub(new DateInterval('P1D'));
        $qb=$this->queryKeyEvent();
        return $qb
            // -> andWhere('ap.starttime <= :now AND ap.endtime >= :now')
            -> andWhere('ap.endtime >= :now')
            -> setParameter('now', $date)
            -> andWhere('lm.id = :id')
            -> setParameter('id', $id)
            -> orderBy('p.create_at', 'ASC')
            -> getQuery()
            -> getResult();

    }
    public function findAllsEventsByOneLocateMedia($id){
        $qb=$this->queryKeyEvent();
        return $qb
            -> andWhere('lm.id = :id')
            -> setParameter('id', $id)
            -> orderBy('p.create_at', 'ASC')
            -> getQuery()
            -> getResult();

    }



    public function findEventByOneLocateMediaAfterToDay($id){
        $date=new dateTime();
        $qb=$this->queryKeyEvent();
        return $qb
            //-> andWhere('ap.starttime >= :now')
            -> andWhere('ap.endtime >= :now')
            -> setParameter('now', $date)
            -> andWhere('lm.id >= :id')
            -> setParameter('id', $id)
            -> orderBy('p.create_at', 'ASC')
            -> getQuery()
            -> getResult();

    }
    public function findAllEventsByIdPotin($id){
        $date=new dateTime();
        $qb=$this->queryKeyEvent();
        return $qb
            //-> andWhere('ap.starttime >= :now')
            -> andWhere('ap.endtime >= :now')
            -> setParameter('now', $date)
            -> andWhere('ptn.id >= :id')
            -> setParameter('id', $id)
            -> orderBy('p.create_at', 'ASC')
            -> getQuery()
            -> getArrayResult();
    }

    public function findLastBeforeWeek(){
        $date=new dateTime();
        $qb=$this->queryKeyEvent();
        return $qb
           // -> andWhere('ap.starttime <= :now AND ap.endtime >= :now')
            -> andWhere('ap.endtime >= :now')
            -> setParameter('now', $date)
            -> orderBy('p.create_at', 'ASC')
            -> getQuery()
            -> getResult();
    }

    public function findLastByCityBeforeWeek($city){ // todo ici plusieru technique pour les dates
        $date=new dateTime();
        //$end = new \DateTimeImmutable();
        //  $start=$end->sub(new DateInterval('P150D'));
        $qb=$this->queryKeyEvent();
        return $qb
            -> andWhere('loc.id =:city')
            -> setParameter('city', $city)
            -> andWhere('ap.starttime <= :now AND ap.endtime >= :now')
            //-> setParameter('start', $start->format('Y-m-d 00:00:00'))
            //-> setParameter('end', $end->format('Y-m-d 23:59:59'))
            // -> setParameter('start', date('Y-m-d', strtotime(' - 300 days')))
            -> setParameter('now', $date)
            //-> setParameter('start', $start) ça marche aussi
            //-> setParameter('end', $end)  comme avec strotime à verifier
            -> orderBy('p.create_at', 'ASC')
            -> getQuery()
            -> getResult();

    }

    public function findEventKey($key){
        $qb=$this->queryKeyEvent();
        return $qb
            -> andWhere('p.keymodule =:key')
            -> setParameter('key', $key)
            -> andwhere('p.deleted = false')
            -> orderBy('p.create_at', 'ASC')
            -> getQuery()
            //-> getResult();
           -> getArrayResult();
    }

    public function findEventById($id){
        $qb=$this->queryKeyEvent();
        return $qb
            -> andWhere('p.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findEventByOneId($id){
        $qb=$this->queryKeyEvent();
        return $qb
            -> andWhere('p.id = :id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getOneOrNullResult();
    }

    public function findEventByOnePotin($id){
        $qb=$this->queryKeyEvent();
        return $qb
            -> andWhere('ptn.id = :id')
            -> setParameter('id', $id)
            -> orderBy('p.create_at', 'ASC')
            -> getQuery()
            -> getResult();

    }

    public function findEventByIdPotin($id){
        $qb=$this->queryEventandPotin();
        return $qb
            -> andWhere('ptn.id = :id')
            -> setParameter('id', $id)
            -> orderBy('p.create_at', 'ASC')
            -> getQuery()
            -> getResult();
    }



    public function queryKeyEvent(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            -> andwhere('p.deleted = false')
            -> leftJoin('p.appointment', 'ap')
            -> addSelect('ap')
            -> leftJoin('ap.idPeriods', 'pr')
            -> addSelect('pr')
            -> leftJoin('ap.tabdate', 'td')
            -> addSelect('td')
            -> leftJoin('ap.localisation', 'loc')
            -> addSelect('loc')
            -> leftJoin('p.tagueries', 'tag')
            -> addSelect('tag')
            -> leftJoin('p.media', 'm')
            -> addSelect('m')
            -> leftJoin('m.imagejpg', 'pic')
            -> addSelect('pic')
            -> leftJoin('p.potin', 'ptn')
            -> addSelect('ptn')
            -> leftJoin('ptn.htmlcontent', 'ctn')
            -> addSelect('ctn')
            -> leftJoin('ptn.media', 'md')
            -> addSelect('md')
            -> leftJoin('p.locatemedia', 'lm')
            -> addSelect('lm')
            -> leftJoin('lm.locality', 'cty')
            -> addSelect('cty')
            -> leftJoin('md.imagejpg', 'jpg')
            -> addSelect('jpg');
    }

    public function queryEventandPotin(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            -> andwhere('p.deleted = false')
            /* -> leftJoin('p.appointment', 'ap')
             -> addSelect('ap')
             -> leftJoin('ap.idPeriods', 'pr')
             -> addSelect('pr')
             -> leftJoin('ap.tabdate', 'td')
             -> addSelect('td')
             -> leftJoin('ap.localisation', 'loc')
             -> addSelect('loc')
             -> leftJoin('p.tagueries', 'tag')
             -> addSelect('tag')
             -> leftJoin('p.media', 'm')
             -> addSelect('m')
             -> leftJoin('m.imagejpg', 'pic')
             -> addSelect('pic')
         */  -> leftJoin('p.potin', 'ptn')
            -> addSelect('ptn')
            -> leftJoin('ptn.htmlcontent', 'ctn')
            -> addSelect('ctn')
            -> leftJoin('ptn.gpreview', 'gprw')
            -> addSelect('gprw')
        /*     -> leftJoin('ptn.media', 'md')
            -> addSelect('md')
        -> leftJoin('gprw.preview', 'rw')
           -> addSelect('rw')   */
            -> leftJoin('ptn.gpressources', 'gprs')
            -> addSelect('gprs');
        /*    -> leftJoin('gprs.resources', 'rs')
            -> addSelect('rs');
        /*  -> leftJoin('p.locatemedia', 'lm')
            -> addSelect('lm')
            -> leftJoin('lm.locality', 'cty')
            -> addSelect('cty')
            -> leftJoin('md.imagejpg', 'jpg')
            -> addSelect('jpg');
        */
    }

    public function querySubEvent(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            -> andwhere('p.deleted = false')
            -> leftJoin('p.appointment', 'ap')
            -> addSelect('ap')
            -> leftJoin('ap.idPeriods', 'pr')
            -> addSelect('pr')
            -> leftJoin('ap.tabdate', 'td')
            -> addSelect('td')
            -> leftJoin('ap.localisation', 'loc')
            -> addSelect('loc')
            -> leftJoin('p.potin', 'ptn')
            -> addSelect('ptn')
            -> leftJoin('p.locatemedia', 'lm')
            -> addSelect('lm')
            -> leftJoin('lm.locality', 'cty')
            -> addSelect('cty')
            -> leftJoin('p.subscription', 'subs')
            -> addSelect('subs')
            -> leftJoin('subs.docs', 'docs')
            -> addSelect('docs')
            -> leftJoin('subs.registered', 'rg')
            -> addSelect('rg');
    }

    // reste a voir ..........



    public function findEventsByKeyWithOutId($key, $id){
        $qb=$this->queryKeyEvent();
        return $qb
            -> andWhere('p.keymodule =:key')
            -> setParameter('key', $key)
            -> andwhere('p.deleted = false')
            -> andwhere('p.id !=:id')
            -> setParameter('id', $id)
            -> orderBy('p.create_at', 'ASC')
            -> getQuery()
            -> getResult();
    }

    public function findlastByKey($key){
        $qb=$this->queryKeyEvent();
        return $qb
            -> andWhere('p.keymodule =:key')
            -> setParameter('key', $key)
            -> orderBy('p.create_at', 'DESC')
            -> setMaxResults(1)
            -> getQuery()
            -> getResult();
    }

    /**
     * @throws NonUniqueResultException
     */


}
