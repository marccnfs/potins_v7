<?php

namespace App\Repository;


use App\Entity\Ressources\Ressources;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ressources|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ressources|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ressources[]    findAll()
 * @method Ressources[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RessourcesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ressources::class);
    }



    public function queryRessourceAll(): QueryBuilder
    {
        return $this->createQueryBuilder('r')
            -> andwhere('r.deleted = false')
            -> leftJoin('r.categorie','ct')
            -> addSelect('ct')
            -> leftJoin('r.pict', 'pi')
            -> addSelect('pi')
            -> leftJoin('r.gpressources', 'gp')
            -> addSelect('gp')
            -> leftJoin('r.htmlcontent', 'ht')
            -> addSelect('ht');
    }

    public function findAllRss(){
        return $this->createQueryBuilder('r')
            -> andwhere('r.deleted = false')
            -> orderBy('r.titre', 'DESC')
            -> getQuery()
            -> getResult();
    }

    public function findRessourcesByGpId($id){
        $qb=$this->queryRessourceAll();
        return $qb
            -> andWhere('gp.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getResult();
    }

    public function searchPublic(?string $keyword, ?int $categoryId): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.deleted = false')
            ->leftJoin('r.categorie', 'ct')
            ->addSelect('ct')
            ->leftJoin('r.pict', 'p')
            ->addSelect('p')
            ->orderBy('r.titre', 'ASC');

        if ($keyword) {
            $normalizedKeyword = mb_strtolower($keyword);
            $qb
                ->andWhere('LOWER(r.titre) LIKE :keyword OR LOWER(r.descriptif) LIKE :keyword OR LOWER(COALESCE(r.infos, \'\')) LIKE :keyword')
                ->setParameter('keyword', '%' . $normalizedKeyword . '%');
        }

        if ($categoryId) {
            $qb
                ->andWhere('ct.id = :category')
                ->setParameter('category', $categoryId);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }


    public function findForIndex(){
        return $this->createQueryBuilder('r')
            -> andwhere('r.deleted = false')
            -> leftJoin('r.categorie','ct')
            -> addSelect('ct')
            -> getQuery()
            -> getResult();
    }

    public function findAllById($id){
        return $this->createQueryBuilder('r')
            -> leftJoin('r.potin', 'p')
            -> addSelect('p')
            -> leftJoin('r.article', 'a')
            -> addSelect('a')
            -> leftJoin('r.pict', 'pt')
            -> addSelect('pt')
            -> andWhere('p.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getResult();
    }

    public function findForEdit($id){
        $qb=$this->queryRessourceAll();
        return $qb
            -> andWhere('r.id =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getResult();
    }

    public function findAllRsscCategoryWithOutRsscId($id, $cat){
        $qb=$this->queryRessourceAll();
        return $qb
            -> andwhere('r.id !=:id')
            -> setParameter('id', $id)
            -> andwhere('ct.id =:cat')
            -> setParameter('cat', $cat)
          //  -> orderBy('r.createAt', 'DESC')
            -> getQuery()
            -> getResult();

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


    public function findByName($q){
        return $this->createQueryBuilder('r')
            -> andWhere('r.titre  LIKE :key')
            -> setParameter('key', '%'.$q.'%')
            -> leftJoin('r.categorie', 'ct')
            -> addSelect('ct')
            -> leftJoin('r.pict', 'p')
            -> addSelect('p')
            -> orderBy('ct.name', 'DESC')
            -> getQuery()
            -> getResult();
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


    private function findActiveQuery()
    {
        return $this->createQueryBuilder('c')
            ->where('c.active = true')
            ->leftJoin('c.categorie','ct')
            -> addSelect('ct');
    }


}
