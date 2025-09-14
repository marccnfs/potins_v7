<?php

namespace App\Repository;

use App\Entity\Customer\Customers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customers>
 *
 * @method Customers|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customers|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customers[]    findAll()
 * @method Customers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customers::class);
    }

    public function save(Customers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Customers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findCustoAndUserById($id){
        return $this->createQueryBuilder('c')
            -> where('c.active = true')
            -> andWhere('c.id =:id')
            -> setParameter('id', $id)
            -> leftJoin('c.profil', 'p')
            -> addSelect('p')
            -> getQuery()
            -> getOneOrNullResult();
    }


    // reste à voir......


    private function findActiveQuery()
    {
        return $this->createQueryBuilder('c')
            ->where('c.active = true');
    }


    private function findOneCustomerByNumClient($numclient)
    {
        return $this->createQueryBuilder('c')
            ->where('c.active = true')
            -> andWhere('c.numclient =:id')
            -> setParameter('id', $numclient);
    }

    public function findLinkProviderForOneCustomerByNumClient($numclient){
        return $this->findOneCustomerByNumClient($numclient)
            -> leftJoin('c.providerlink', 'p')
            -> addSelect('p')
            -> andWhere('p.active = true')
            -> getQuery()
            -> getResult();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function allInfo($id){
        return $this->createQueryBuilder('c')
            -> andWhere('c.id =:id')
            -> setParameter('id', $id)
            -> leftJoin('c.user', 'u')
            -> addSelect('u')
            -> leftJoin('c.profil', 'p')
            -> addSelect('p')
            -> leftJoin('c.numclient', 'cl')
            -> addSelect('cl')
            -> leftJoin('c.dispatchspace', 'd')
            -> addSelect('d')
            -> leftJoin('d.spwsite', 'sp')
            -> addSelect('sp')
            -> leftJoin('sp.website', 'w')
            -> addSelect('w')
            -> leftJoin('w.module', 'mw')
            -> addSelect('mw')
            -> leftJoin('d.locality', 'l')
            -> addSelect('l')
            -> leftJoin('cl.orders', 'o')
            -> addSelect('o')
            -> orderBy('c.create_at')
            -> getQuery()->getResult();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findAllCustoAndUserActive(){
        return $this->createQueryBuilder('c')
            -> where('c.active = true')
            -> leftJoin('c.user', 'u')
            -> addSelect('u')
            -> leftJoin('c.profil', 'p')
            -> addSelect('p')
            -> orderBy('c.create_at', 'DESC')
            -> getQuery()->getResult();
    }
}
