<?php

namespace App\Repository;

use App\Entity\Marketplace\Presents;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Presents>
 *
 * @method Presents|null find($id, $lockMode = null, $lockVersion = null)
 * @method Presents|null findOneBy(array $criteria, array $orderBy = null)
 * @method Presents[]    findAll()
 * @method Presents[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PresentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Presents::class);
    }

    public function queryRessourceAll(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            -> andwhere('p.deleted = false')
            -> leftJoin('p.categorie','ct')
            -> addSelect('ct')
            -> leftJoin('p.pict', 'pi')
            -> addSelect('pi')
            -> leftJoin('p.htmlcontent', 'ht')
            -> addSelect('ht');
    }

    public function findAllByOffre($id){
        return $this->createQueryBuilder('p')
            -> leftJoin('p.offre', 'o')
            -> addSelect('o')
            -> leftJoin('p.article', 'a')
            -> addSelect('a')
            -> leftJoin('a.pict', 'pt')
            -> addSelect('pt')
            -> andWhere('o.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getResult();
    }

    public function findForEdit($id){
        $qb=$this->queryRessourceAll();
        return $qb
            -> andWhere('p.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getResult();
    }

}
