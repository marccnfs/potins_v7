<?php

namespace App\Repository;

use App\Entity\Marketplace\GpPresents;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GpPresents>
 *
 * @method GpPresents|null find($id, $lockMode = null, $lockVersion = null)
 * @method GpPresents|null findOneBy(array $criteria, array $orderBy = null)
 * @method GpPresents[]    findAll()
 * @method GpPresents[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GpPresentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GpPresents::class);
    }
    public function save(GpPresents $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GpPresents $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function queryFormule(): QueryBuilder
    {
        return $this->createQueryBuilder('f')
            -> andwhere('f.deleted = false')
            -> leftJoin('f.services', 's')
            -> addSelect('s')
            -> leftJoin('f.catformules', 'c')
            -> addSelect('c')
            -> leftJoin('c.declinaison', 'd')
            -> addSelect('d')
            -> leftJoin('f.articles', 'a')
            -> addSelect('a')
            -> leftJoin('a.pict', 'p')
            -> addSelect('p')
            -> leftJoin('a.categorie', 'ct')
            -> addSelect('ct')
            -> leftJoin('f.pictformule', 'pf')
            -> addSelect('pf');
    }
}
