<?php

namespace App\Repository;

use App\Entity\LogMessages\Msgs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Msgs>
 *
 * @method Msgs|null find($id, $lockMode = null, $lockVersion = null)
 * @method Msgs|null findOneBy(array $criteria, array $orderBy = null)
 * @method Msgs[]    findAll()
 * @method Msgs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MsgsRepository extends ServiceEntityRepository
{
    public const PAGINATOR_PER_PAGE = 25;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Msgs::class);
    }

    public function save(Msgs $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Msgs $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findConversationByMsgId($id){
        return $this->createQueryBuilder('m')
            ->leftJoin('m.msgwebsite','ms')
            ->addSelect('ms')
            ->leftJoin('m.tabreaders','tb')
            ->addSelect('tb')
            ->leftJoin('tb.dispatch','d')
            ->addSelect('d')
            ->andWhere('ms.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findMsgspaginator($id, int $offset): Paginator
    {
        $q = $this->createQueryBuilder('msg')

            -> leftJoin('msg.msgwebsite','mw')
            -> addSelect('mw')
            -> leftJoin('msg.tabreaders','tb')
            -> addSelect('tb')
            -> andWhere('mw.websitedest  = :id')
            -> setParameter('id', $id)
            -> orderBy('msg.create_at', 'ASC')
            -> setFirstResult($offset)
            -> setMaxResults(self::PAGINATOR_PER_PAGE)
            -> getQuery()
            -> getResult();

        return new Paginator($q,$fetchJoinCollection = true);

    }

    public function findMsgsQuery($id): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('msg')
            -> leftJoin('msg.msgwebsite','mw')
            -> addSelect('mw')
            -> leftJoin('msg.tabreaders','tb')
            -> addSelect('tb')
            -> andWhere('mw.websitedest  = :id')
            -> setParameter('id', $id)
            -> orderBy('msg.create_at', 'ASC')
            -> getQuery();
    }

}
