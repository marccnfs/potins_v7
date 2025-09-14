<?php

namespace App\Repository;

use App\Entity\LogMessages\PrivateConvers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrivateConvers>
 *
 * @method PrivateConvers|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrivateConvers|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrivateConvers[]    findAll()
 * @method PrivateConvers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrivateConversRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrivateConvers::class);
    }

    public function save(PrivateConvers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PrivateConvers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findPrivateConversAndMsgById($id)
    {
        return $this->createQueryBuilder('pc')
            ->leftJoin('pc.websitedest', 'w')
            ->addSelect('w')
            ->leftJoin('pc.msgs','msgs')
            ->addSelect('msgs')
            ->leftJoin('msgs.tabreaders','tb')
            ->addSelect('tb')
            ->leftJoin('pc.dispatchdest','dest')
            ->addSelect('dest')
            ->leftJoin('pc.dispatchopen','exp')
            ->addSelect('exp')
            ->andWhere('pc.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findMsgswebsiteQuery($id): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('pc')
            ->leftJoin('pc.websitedest', 'w')
            ->addSelect('w')
            ->leftJoin('pc.msgs','msgs')
            ->addSelect('msgs')
            ->leftJoin('msgs.tabreaders','tb')
            ->addSelect('tb')
            ->leftJoin('pc.dispatchdest','dest')
            ->addSelect('dest')
            ->leftJoin('pc.dispatchopen','exp')
            ->addSelect('exp')
            -> orderBy('pc.create_at', 'DESC')
            -> getQuery();
    }

    public function findMsgsdispatchQuery($id): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('pc')
            ->leftJoin('pc.websitedest', 'w')
            ->addSelect('w')
            ->leftJoin('pc.msgs','msgs')
            ->addSelect('msgs')
            ->leftJoin('msgs.tabreaders','tb')
            ->addSelect('tb')
            ->leftJoin('pc.dispatchdest','dest')
            ->addSelect('dest')
            ->leftJoin('pc.dispatchopen','exp')
            ->addSelect('exp')
            ->andWhere("exp.id  = ?1 OR dest.id  = ?1")
            ->setParameter(1, $id)
            ->orderBy('pc.create_at', 'DESC')
            ->getQuery();
    }
}
