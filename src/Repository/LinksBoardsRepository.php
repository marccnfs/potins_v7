<?php

namespace App\Repository;

use App\Entity\Boards\LinksBoards;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LinksBoards>
 *
 * @method LinksBoards|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinksBoards|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinksBoards[]    findAll()
 * @method LinksBoards[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinksBoardsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinksBoards::class);
    }

    public function save(LinksBoards $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LinksBoards $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
