<?php

namespace App\Repository;

use App\Entity\Admin\Products;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @method Products|null find($id, $lockMode = null, $lockVersion = null)
 * @method Products|null findOneBy(array $criteria, array $orderBy = null)
 * @method Products[]    findAll()
 * @method Products[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductsRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Products::class);
    }

    /**
     * @param $categorie
     * @return Query
     */
    function produitsByCategorieQuery($categorie) : Query
    {

        $query = $this->createQueryBuilder('p')
            ->where('p.categorie = :categorie')
            ->andWhere('p.disponible = 1')
            ->setParameter('categorie', $categorie)
            ->orderBy('p.id', 'ASC')
            ->getQuery();
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneProduct($name)
    {
        return $this->createQueryBuilder('p')
            ->where('p.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $categorie
     * @return mixed
     */
    function produitsByCategorie($categorie){

        $query = $this->createQueryBuilder('u')
            ->where('u.categorie = :categorie')
            ->andWhere('u.disponible = 1')
            ->setParameter('categorie', $categorie)
            ->orderBy('u.id', 'ASC')
            ->getQuery();

        $produits = $query->getResult();
        return $produits;
    }

    /**
     * @param $chaine
     * @return mixed
     */
    function rechercheProduit($chaine){
        /*
                    $query = $this->getEntityManager()->createQuery(
                        'SELECT u
                            FROM BenEcommerceBundle:Produits u
                            WHERE u.nom like :chaine
                            ORDER BY u.id ASC'
                  )->setParameter('chaine', '%'.$chaine.'%');

         */

        $query = $this->createQueryBuilder('p')
            ->where('p.nom like :chaine')
            ->setParameter('chaine', '%'.$chaine.'%')
            ->orderBy('p.id', 'ASC')
            ->getQuery();

        $produits = $query->getResult();
        return $produits;
    }


    /**
     * @param
     * @return mixed
     */
    function findArray($array){

        $query = $this->createQueryBuilder('u')
            ->where('u.id IN(:array)')
            ->setParameter('array', $array)
            ->orderBy('u.id', 'ASC')
            ->getQuery();

        $produits = $query->getResult();
        return $produits;
    }

}
