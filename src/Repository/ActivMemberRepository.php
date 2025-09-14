<?php

namespace App\Repository;

use App\Entity\Member\Activmember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activmember>
 *
 * @method Activmember|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activmember|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activmember[]    findAll()
 * @method Activmember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activmember::class);
    }

    public function save(Activmember $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Activmember $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param $idcustomer
     * @return mixed
     */
    public function findwithidcustomerAll($idcustomer): mixed
    {
        return $this->createQueryBuilder('a')
            -> leftJoin('a.customer', 'c')
            -> addSelect('c')
            -> leftJoin('c.profil','p')
            -> addSelect('p')
            -> leftJoin('c.services','sv')
            -> addSelect('sv')
            -> leftJoin('a.memberlinks', 'l')
            -> addSelect('l')
            -> leftJoin('a.tbnotifs', 'tbn')
            -> addSelect('tbn')
            -> leftJoin('a.locality', 'loc')
            -> addSelect('loc')
            -> leftJoin('a.boardslist', 'bdl')
            -> addSelect('bdl')
            -> leftJoin('bdl.board', 'b')
            -> addSelect('b')
            -> leftJoin('b.locality', 'lb')
            -> addSelect('lb')
            -> leftJoin('b.template', 't')
            -> addSelect('t')
            -> leftJoin('t.logo', 'lg')
            -> addSelect('lg')
            -> leftJoin('b.boardpartner', 'gp')
            -> addSelect('gp')
            -> andWhere('c.id =:id')
            -> setParameter('id', $idcustomer)
            -> andWhere('bdl.role = :admin OR bdl.role = :super')
            -> setParameter('super', 'superadmin')
            -> setParameter('admin', 'admin')
            -> getQuery()
            -> getOneOrNullResult();
    }

    public function findAllById($id): mixed
    {
        return $this->createQueryBuilder('a')
            -> leftJoin('a.customer', 'c')
            -> addSelect('c')
            -> leftJoin('c.profil','p')
            -> addSelect('p')
            -> leftJoin('c.services','sv')
            -> addSelect('sv')
            -> leftJoin('a.memberlinks', 'l')
            -> addSelect('l')
            -> leftJoin('a.tbnotifs', 'tbn')
            -> addSelect('tbn')
            -> leftJoin('a.locality', 'loc')
            -> addSelect('loc')
            -> leftJoin('a.boardslist', 'bdl')
            -> addSelect('bdl')
            -> leftJoin('bdl.board', 'b')
            -> addSelect('b')
            -> leftJoin('b.locality', 'lb')
            -> addSelect('lb')
            -> leftJoin('b.template', 't')
            -> addSelect('t')
            -> leftJoin('t.logo', 'lg')
            -> addSelect('lg')
            -> andWhere('a.id =:id')
            -> setParameter('id', $id)
           // -> andWhere('bdl.role = :admin OR bdl.role = :super')
           // -> setParameter('super', 'superadmin')
           // -> setParameter('admin', 'admin')
            -> getQuery()
            -> getOneOrNullResult();
    }



    //reste à voir .....



    public function queryDispacthById($id)
    {
        return $this->createQueryBuilder('d')
            -> andWhere('d.id =:id')
            -> setParameter('id', $id);
    }

    public function findForInit($id)
    {
        return $this->queryDispacthById($id)
            -> leftJoin('d.customer', 'c')
            -> addSelect('c')
            -> getQuery()
            -> getOneOrNullResult();
    }


    public function finddispatchmail($email): mixed
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.customer','c')
            ->addSelect('c')
            ->leftJoin('c.profil','p')
            ->addSelect('p')
            ->andWhere('c.emailcontact = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $id
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findwithAll($id)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.id = :val')
            ->setParameter('val', $id)
            ->leftJoin('d.spwsite','s')
            ->addSelect('s')
            ->leftJoin('d.sector','sc')
            ->addSelect('sc')
            ->leftJoin('sc.adresse','ad')
            ->addSelect('ad')
            ->leftJoin('s.website','w')
            ->addSelect('w')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findwithAllArray($id)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.id = :val')
            ->setParameter('val', $id)
            ->leftJoin('d.spwsite','s')
            ->addSelect('s')
            ->leftJoin('s.website','w')
            ->addSelect('w')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param $id
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findLinkContact($id){
        return $this->createQueryBuilder('d')
            ->andWhere('d.id = :val')
            ->setParameter('val', $id)
            ->leftJoin('d.spwsite','s')
            ->addSelect('s')
            ->leftJoin('s.website','w')
            ->addSelect('w')
            ->leftJoin('w.module','m')
            ->addSelect('m')
            ->andWhere('m.typemodule = :contact')
            ->setParameter('contact', 'contact')
            ->andWhere('s.role = :admin')
            ->setParameter('admin', 'admin')
            ->getQuery()
            ->getOneOrNullResult();
    }



    public function findPostByAppointWithPeriodForOneTeam($id, $start, $end){
        return $this->queryDispacthById($id)
            -> leftJoin('d.template', 't')
            -> addSelect('t')
            -> leftJoin('d.module', 'm')
            -> addSelect('m')
            -> leftJoin('m.postevent', 'e')
            -> addSelect('e')
            -> leftjoin('m.appointment', 'a')
            -> addSelect('a')
            -> andWhere('a.starttime <= :end')
            -> andWhere('a.endtime >= :start')  //TODO c'est faux ......
            -> setParameter('start', $start->format('Y-m-d 00:00:00'))
            -> setParameter('end', $end->format('Y-m-d 23:59:59'))
            -> orderBy('p.startPeriod', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPostForAllSpaceWeb(){
        return $this->findActiveQuery()
            -> leftJoin('d.template', 't')
            -> addSelect('t')
            -> leftJoin('d.module', 'm')
            -> addSelect('m')
            -> leftJoin('m.postevent', 'e')
            -> addSelect('e')
            -> leftJoin('m.appointment', 'a')
            -> addSelect('a')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param $nameSpaceWeb
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findSpaceWebByName($nameSpaceWeb){
        return $this->findActiveQuery()
            ->andWhere('p.name =:name')
            ->setParameter('name', $nameSpaceWeb)
            ->getQuery()
            ->getOneOrNullResult();
    }


    private function findActiveQuery()
    {
        return $this->createQueryBuilder('d')
            ->where('d.active = true');
    }

    /**
     * @param $id
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findandWebsite($id)
    {
        return $this->queryDispacthById($id)
            -> leftJoin('d.website', 'w')
            -> addSelect('w')
            -> getQuery()
            -> getOneOrNullResult();
    }

    /**
     * @param $id
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findSpaceWebsById($id)
    {
        return $this->createQueryBuilder('d')
            -> where('d.active = true')
            -> andWhere('d.idUser =:id')
            -> setParameter('id', $id)
            -> getQuery()
            -> getOneOrNullResult();
    }

    public function findInfoObj($id){

        return $this->queryDispacthById($id)
            -> leftJoin('d.template', 't')
            -> addSelect('t')
            -> leftJoin('d.customer', 'c')
            -> addSelect('c')
            -> leftJoin('d.localisation', 'l')
            -> addSelect('l')
            -> leftJoin('t.sector', 's')
            -> addSelect('s')
            -> leftJoin('s.adresse', 'a')
            -> addSelect('a')
            -> getQuery()
            -> getOneOrNullResult();
    }

    public function findWhithOpendaysAndTemplate($id){
        return $this->queryDispacthById($id)
            -> leftJoin('d.template', 't')
            -> addSelect('t')
            -> leftJoin('t.logo', 'l')
            -> addSelect('l')
            -> leftJoin('t.sector', 's')
            -> addSelect('s')
            -> leftJoin('d.website', 'w')
            -> addSelect('w')
            -> leftJoin('w.tabopendays', 'o')
            -> addSelect('o')
            -> getQuery()
            -> getOneOrNullResult();
    }
}
