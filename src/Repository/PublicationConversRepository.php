<?php

namespace App\Repository;

use App\Entity\LogMessages\PublicationConvers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PublicationConvers>
 *
 * @method PublicationConvers|null find($id, $lockMode = null, $lockVersion = null)
 * @method PublicationConvers|null findOneBy(array $criteria, array $orderBy = null)
 * @method PublicationConvers[]    findAll()
 * @method PublicationConvers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicationConversRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicationConvers::class);
    }

    public function save(PublicationConvers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PublicationConvers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllMsgPublicationQuery($id): \Doctrine\ORM\Query //todo si taball ne fonctionne pas
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.tballmsgp', 'tp')
            ->addSelect('tp')
            ->leftJoin('tp.dispatch', 'd')
            ->addSelect('d')
            ->leftJoin('p.msgs', 'mp')
            ->addSelect('mp')
            ->leftJoin('mp.tabreaders','tr')
            ->addSelect('tr')
            ->andWhere('d.id = :val')
            ->setParameter('val', $id)
            ->orderBy('p.create_at', 'DESC')
            ->getQuery();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findMsgById($id){
        return $this->createQueryBuilder('p')
            ->leftJoin('p.msgs','ms')
            ->addSelect('ms')
            ->leftJoin('ms.tabreaders','tr')
            ->addSelect('tr')
            ->leftJoin('tr.tabnotifs','tn')
            ->addSelect('tn')
            ->leftJoin('p.tballmsgp','ta')
            ->addSelect('ta')
            ->leftJoin('ta.dispatch','d')
            ->addSelect('d')
            ->leftJoin('ta.contact','co')
            ->addSelect('co')
            ->leftJoin('p.tabpublication','pu')
            ->addSelect('pu')
            ->leftJoin('pu.post','pp')
            ->addSelect('pp')
            ->leftJoin('pu.offre','pf')
            ->addSelect('pf')
            ->leftJoin('co.useridentity', 'idd')
            ->addSelect('idd')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByIdWithAll($id): ?PublicationConvers
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.tabpublication', 'tp')
            ->addSelect('tp')
            ->leftJoin('tp.post', 'po')
            ->addSelect('po')
            ->leftJoin('p.msgs', 'msg')
            ->addSelect('msg')
            ->leftJoin('msg.tabreaders', 'tr')
            ->addSelect('tr')
            ->leftJoin('tr.tabnotifs', 'nt')
            ->addSelect('nt')
            ->andWhere('p.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findPublicationConversAndMsgById($id)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.tballmsgp', 't')
            ->addSelect('t')
            ->leftJoin('p.tabpublication','tp')
            ->addSelect('tp')
            ->leftJoin('p.msgs','mgs')
            ->addSelect('mgs')
            ->andWhere('p.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
