<?php

namespace App\Repository;

use App\Entity\Module\Contactation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contactation>
 *
 * @method Contactation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contactation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contactation[]    findAll()
 * @method Contactation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contactation::class);
    }

    public function save(Contactation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Contactation $entity, bool $flush = false): void
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
    public function findMsgContactBy($id)
    {
        return $this->createQueryBuilder('c')
            -> andWhere('c.id = :id')
            -> setParameter('id', $id)
            -> leftJoin('c.messages','msg')
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
    /**
     * @param $id
     * @return mixed
     * @throws NonUniqueResultException
     * requete pour creation de message vers un website
     */
    public function findContactationByApi($id)
    {
        return $this->createQueryBuilder('c')
            -> andWhere('c.id = :id')
            -> setParameter('id', $id)
            -> leftJoin('c.moduletype', 'm')
            -> addSelect('m')
            -> leftjoin('m.website', 'w')
            -> addSelect('w')
            -> leftjoin('w.template', 'tp')
            -> addSelect('tp')
            -> leftJoin('w.locality', 'l')
            -> addSelect('l')
            -> leftJoin('tp.sector', 's')
            -> addSelect('s')
            -> leftJoin('s.adresse', 'a')
            -> addSelect('a')
            -> leftJoin('w.spwsites', 'spw')
            -> addSelect('spw')
            -> andWhere('spw.role = :adm')
            -> setParameter('adm', "admin")
            -> leftjoin('spw.disptachwebsite', 'dsp')
            -> addSelect('dsp')
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @param $key
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findContactationByKey($key): mixed
    {
        return $this->createQueryBuilder('c')
            -> leftJoin('c.moduletype', 'm')
            -> addSelect('m')
            -> leftjoin('m.website', 'w')
            -> addSelect('w')
            -> leftjoin('w.spwsites', 'spw')
            -> addSelect('spw')
            -> leftjoin('w.template', 'tp')
            -> addSelect('tp')
            -> leftJoin('w.locality', 'l')
            -> addSelect('l')
            -> leftJoin('tp.sector', 's')
            -> addSelect('s')
            -> leftJoin('s.adresse', 'a')
            -> addSelect('a')
            -> andWhere('c.keycontactation = :keyc')
            -> setParameter('keyc', $key)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }


    /**
     * @param $key
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findWithMsgByKey($key){
        return $this->createQueryBuilder('c')
            -> leftJoin('c.moduletype', 'm')
            -> addSelect('m')
            -> leftjoin('m.website', 'w')
            -> addSelect('w')
            -> leftJoin('w.locality','l')
            -> addSelect('l')
            -> leftJoin('w.template', 't')
            -> addSelect('t')
            -> leftJoin('t.logo', 'lg')
            -> addSelect('lg')
            -> leftJoin('t.tagueries', 'tag')
            -> addSelect('tag')
            -> leftJoin('c.messages', 'msg')
            -> addSelect('msg')
            -> andWhere('c.keycontactation = :keyc')
            -> setParameter('keyc', $key)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @param $key
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findMsgByKey($key){
        return $this->createQueryBuilder('c')
            -> leftJoin('c.moduletype', 'm')
            -> addSelect('m')
            -> leftjoin('m.website', 'w')
            -> addSelect('w')
            -> leftJoin('w.spwsites', 'pws')
            -> addSelect('pws')
            -> leftJoin('pws.disptachwebsite', 'd')
            -> addSelect('d')
            -> leftJoin('w.locality','l')
            -> addSelect('l')
            -> leftJoin('w.template', 't')
            -> addSelect('t')
            -> leftJoin('t.logo', 'lg')
            -> addSelect('lg')
            -> andWhere('c.keycontactation = :keyc')
            -> setParameter('keyc', $key)
            -> getQuery()
            -> getOneOrNullResult()
            ;
    }

    /**
     * @param $key
     * @param $dispatch
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findContactationByKeyForOneDispacth($key, $dispatch){
        return $this->createQueryBuilder('c')
            -> leftJoin('c.moduletype', 'm')
            -> addSelect('m')
            -> leftjoin('m.website', 'w')
            -> addSelect('w')
            -> leftjoin('w.template', 'tp')
            -> addSelect('tp')
            -> leftJoin('w.locality', 'l')
            -> addSelect('l')
            -> Join('c.messages', 'msg')
            -> addSelect('msg')
            -> andWhere('c.keycontactation = :keyc')
            -> setParameter('keyc', $key)
            //-> andWhere('msg.spacewebexpe = :dispatch')
            //-> setParameter('dispatch', $dispatch)
            -> getQuery()
            -> getOneOrNullResult()
            ;
    }

    /**
     * @param $id
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findContactationByModuleAndIdDispatch($id){
        return $this->createQueryBuilder('c')
            -> leftJoin('c.moduletype', 'm')
            -> addSelect('m')
            -> leftjoin('m.website', 'w')
            -> addSelect('w')
            -> andWhere('c.deleted = false')
            -> andWhere('m.typemodule = :type')
            -> andWhere('w.id = :id')
            -> setParameter('id', $id)
            -> setParameter('type', 'contact')
            -> leftJoin('c.messages', 'msg')
            -> addSelect('msg')
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @param $id
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findCTQ1($id){
        return $this->createQueryBuilder('c')
            -> leftJoin('c.moduletype', 'm')
            -> addSelect('m')
            -> andWhere('c.deleted = false')
            -> andWhere('m.id = :id')
            -> setParameter('id', $id)
            -> leftJoin('c.messages', 'msg')
            -> addSelect('msg')
            -> getQuery()
            -> getOneOrNullResult()
            ;
    }

    public function findMsgNoRead($id)  //todo pas bon a verfif
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.messages', 'msg','WITH', 'msg.isDestRead = false')
            ->addSelect('msg')
            ->andWhere('c.id = :val')
            ->setParameter('val', $id)
            ->orderBy('msg.create_at', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
