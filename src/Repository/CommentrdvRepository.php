<?php

namespace App\Repository;

use App\Entity\Users\Commentrdv;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<\App\Entity\Users\Commentrdv>
 *
 * @method Commentrdv|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commentrdv|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commentrdv[]    findAll()
 * @method Commentrdv[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentrdvRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentrdv::class);
    }

    /**
     * @return list<Commentrdv>
     */
    public function findRecentAgendaRequests(int $limit = 5): array
    {
        return $this->createAgendaRequestQueryBuilder()
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<Commentrdv>
     */
    public function findAllAgendaRequests(): array
    {
        return $this->createAgendaRequestQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    public function countAgendaRequests(): int
    {
        return (int) $this->createQueryBuilder('request')
            ->select('COUNT(request.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function createAgendaRequestQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('request')
            ->leftJoin('request.contact', 'contact')->addSelect('contact')
            ->leftJoin('contact.useridentity', 'profile')->addSelect('profile')
            ->orderBy('request.createAt', 'DESC');
    }
}
