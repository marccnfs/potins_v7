<?php

namespace App\Repository;

use App\Entity\Notifications\Notifmember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notifmember>
 *
 * @method Notifmember|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notifmember|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notifmember[]    findAll()
 * @method Notifmember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotifmemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notifmember::class);
    }

    public function save(Notifmember $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Notifmember $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findBlogBynotifId($id)
    {
        return $this->createQueryBuilder('n')
            ->leftJoin('n.tabmsgp', 'tb')
            ->addSelect( 'tb')
            ->leftJoin('tb.idmessage', 'msg')
            ->addSelect( 'msg')
            ->leftJoin('msg.publicationmsg', 'pmsg')
            ->addSelect( 'pmsg')
            ->leftJoin('pmsg.tabpublication', 'tbp')
            ->addSelect( 'tbp')
            ->leftJoin('tbp.post', 'p')
            ->addSelect( 'p')
            ->andWhere('n.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findAllBynotifId($id)
    {
        return $this->createQueryBuilder('n')
            ->leftJoin('n.tabmsgp', 'tb')
            ->addSelect( 'tb')
            ->leftJoin('n.tabmsgd', 'td')
            ->addSelect( 'td')
            ->leftJoin('n.tabmsgs', 'ts')
            ->addSelect( 'ts')
            ->andWhere('n.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
