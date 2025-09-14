<?php

namespace App\Repository;

use App\Entity\Posts\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function save(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findAllByKey($key){
        return $this->createQueryBuilder('a')
            -> andWhere('a.keymodule =:key')
            -> setParameter('key', $key)
            -> leftJoin('a.categorie', 'ct')
            -> addSelect('ct')
            -> leftJoin('a.pict', 'p')
            -> addSelect('p')
            -> orderBy('ct.name', 'DESC')
            -> getQuery()
            -> getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findWithPostById($id){
        return $this->createQueryBuilder('a')
            -> andWhere('a.id =:id')
            -> setParameter('id', $id)
            -> leftJoin('a.potin', 'pt')
            -> addSelect('pt')
            -> getQuery()
            -> getOneOrNullResult();

    }

    public function findCarteAll($search)
    {
        $qr = $this->findActiveQuery();

        if($search->getMinPrix()){
            $qr = $qr
                ->andWhere('c.prix > :minprice')
                ->setParameter('minprice', $search->getMinPrix());
        }

        if($search->getMaxPrix()){
            $qr = $qr
                ->andWhere('c.prix < :maxprice')
                ->setParameter('maxprice', $search->getMaxPrix());
        }

        if($search->getServices()->count()>0){
            $k=0;
            foreach ($search->getServices() as $service) {
                $k++;
                $qr = $qr
                    ->andWhere(":service$k MEMBER OF c.services")
                    ->setParameter("service$k", $service);
            }
        }

        $qr->orderBy('c.prix', 'ASC');

        return $qr->getQuery();
    }

    public function findtyp($i){
        return $this->findActiveQuery()
            ->andWhere('ct.name Like :type')
            ->setParameter('type', $i);
    }

    public function findCarteService(): array
    {
        return $this->findActiveQuery()
            //->setMaxResults(4)
            ->getQuery()
            ->getResult();
    }


    private function findActiveQuery(): \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->where('c.active = true')
            ->leftJoin('c.categorie','ct')
            -> addSelect('ct');
    }
}
