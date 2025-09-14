<?php

namespace App\Repository;

use App\Entity\LogMessages\MsgBoard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MsgBoard>
 *
 * @method MsgBoard|null find($id, $lockMode = null, $lockVersion = null)
 * @method MsgBoard|null findOneBy(array $criteria, array $orderBy = null)
 * @method MsgBoard[]    findAll()
 * @method MsgBoard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MsgWebisteRepository extends ServiceEntityRepository
{
    public const PAGINATOR_PER_PAGE = 20;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MsgBoard::class);
    }

    public function save(MsgBoard $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MsgBoard $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllMsgWebsiteQuery($id): Query //todo si taball ne fonctionne pas
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.tballmsgs', 'ts')
            ->addSelect('ts')
            ->leftJoin('ts.dispatch', 'd')
            ->addSelect('d')
            ->leftJoin('m.msgs', 'ms')
            ->addSelect('ms')
            ->leftJoin('ms.tabreaders','tr')
            ->addSelect('tr')
            ->andWhere('d.id = :val')
            ->setParameter('val', $id)
            ->orderBy('m.create_at', 'DESC')
            ->getQuery();
    }

    public function findAllAndTbreader(){
        return $this->createQueryBuilder('m')
            ->leftJoin('m.websitedest', 'w')
            ->addSelect('w')
            ->leftJoin('w.spwsites', 'pw')
            ->addSelect('pw')
            ->leftJoin('pw.disptachwebsite', 'd')
            ->addSelect('d')
            ->leftJoin('m.msgs','ms')
            ->addSelect('ms')
            ->leftJoin('ms.tabreaders','tr')
            ->addSelect('tr')
            ->leftJoin('tr.tabnotifs','tn')
            ->addSelect('tn')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findMsgById($id){
        return $this->createQueryBuilder('m')
            ->leftJoin('m.websitedest', 'w')
            ->addSelect('w')
            ->leftJoin('w.spwsites', 'pw')
            ->addSelect('pw')
            ->leftJoin('m.msgs','ms')
            ->addSelect('ms')
            ->leftJoin('ms.tabreaders','tr')
            ->addSelect('tr')
            ->leftJoin('tr.tabnotifs','tn')
            ->addSelect('tn')
            ->leftJoin('m.tballmsgs','ta')
            ->addSelect('ta')
            ->leftJoin('ta.dispatch','d')
            ->addSelect('d')
            ->leftJoin('ta.contact','co')
            ->addSelect('co')
            ->leftJoin('co.useridentity', 'idd')
            ->addSelect('idd')
            ->andWhere('m.id = :id')
            ->setParameter('id', $id)
            ->orderBy('ms.create_at', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findMsgswebsiteQuery($id): Query
    {
        return $this->createQueryBuilder('m')
            -> leftJoin('m.websitedest', 'w')
            -> addSelect('w')
            -> leftJoin('m.tballmsgs', 'tb')
            -> addSelect('tb')
            -> leftJoin('tb.dispatch', 'td')
            -> addSelect('td')
            -> leftJoin('tb.contact', 'tc')
            -> addSelect('tc')
            -> leftJoin('tc.useridentity', 'cp')
            -> addSelect('cp')
            -> leftJoin('m.msgs','msgs')
            -> addSelect('msgs')
            -> leftJoin('msgs.tabreaders','tbr')
            -> addSelect('tbr')
            -> andWhere('m.websitedest  = :id')
            -> setParameter('id', $id)
            -> orderBy('m.create_at', 'DESC')
            -> getQuery();
    }


    /*----------------------------------- après non verfif ----------------------------*/

    public function findConversationByMsgId($id){
        return $this->createQueryBuilder('m')
            ->leftJoin('m.msgs','ms')
            ->addSelect('ms')
            ->leftJoin('ms.tabreaders','tb')
            ->addSelect('tb')
            ->leftJoin('tb.dispatch','d')
            ->addSelect('d')
            ->andWhere('m.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findAllForOneDispatchThanWebsite($dispatch,$contactation)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.contactation = :conta')
            ->setParameter('conta', $contactation)
            ->andWhere('m.spacewebexpe = :dispa')
            ->setParameter('dispa', $dispatch)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findMsgsWebsiteForDispatch($id, $disptch)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.websitedest', 'w')
            ->addSelect('w')
            ->andWhere('w.id = :id')
            ->setParameter('id', $id)
            ->leftJoin('m.msgs','msgs')
            ->addSelect('msgs')
            ->leftJoin('msgs.tabreaders','tb')
            ->addSelect('tb')
            ->leftJoin('tb.dispatch','d')
            ->addSelect('d')
            ->andWhere('tb.dispatch = :disptch')
            ->setParameter('disptch', $disptch)
            ->orderBy('m.create_at', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findMsgsWebsite($id)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.websitedest  = :id')
            ->setParameter('id', $id)
            ->leftJoin('m.msgs','msgs')
            ->addSelect('msgs')
            ->leftJoin('msgs.tabreaders','tb')
            ->addSelect('tb')
            ->setMaxResults(25)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findMsgsWebsitepaginator($id, int $offset): Paginator
    {
        $q = $this->createQueryBuilder('msg')
            -> andWhere('msg.websitedest  = :id')
            -> setParameter('id', $id)
            -> leftJoin('msg.msgs','msgs')
            -> addSelect('msgs')
            //    -> leftJoin('msgs.tabreaders','tb')
            //    -> addSelect('tb')
            -> orderBy('msg.create_at', 'ASC')
            -> setFirstResult($offset)
            -> setMaxResults(self::PAGINATOR_PER_PAGE)
            -> getQuery()
            -> getResult();

        return new Paginator($q,$fetchJoinCollection = true);

    }





    public function findConversationById($id, $disptch)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.id = :id')
            ->setParameter('id', $id)
            ->leftJoin('m.msgs','msgs')
            ->addSelect('msgs')
            ->leftJoin('msgs.tabreaders','tb')
            ->addSelect('tb')
            ->leftJoin('tb.dispatch','d')
            ->addSelect('d')
            ->andWhere('tb.dispatch = :disptch')
            ->setParameter('disptch', $disptch)
            ->orderBy('msgs.create_at', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }
}
