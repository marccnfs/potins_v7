<?php

namespace App\Repository;

use App\Entity\Users\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;


/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function findAllCustomByUserId($id){
        return $this->createQueryBuilder('u')
            -> leftJoin('u.customer', 'c')
            -> addSelect('c')
            -> andWhere('u.id =:id')
            -> setParameter('id', $id)
            -> leftJoin('c.profil', 'p')
            -> addSelect('p')
            -> leftJoin('p.avatar', 'a')
            -> addSelect('a')
            -> leftJoin('c.member', 'd')
            -> addSelect('d')
            -> leftJoin('d.locality', 'loc')
            -> addSelect('loc')
            -> getQuery()
            -> getOneOrNullResult();
    }

    private function findActiveQuery()
    {
        return $this->createQueryBuilder('u')
            ->where('u.enabled = true');
    }

    private function findEmailCanonicalQuery($email)
    {
        return $this->createQueryBuilder('u')
            ->where('u.emailCanonical = :mailCanonical')
            -> setParameter('mailCanonical', $email);
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
     * @param $email
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findUserByEmail($email)
    {
        return $this->findEmailCanonicalQuery($email)
            -> getQuery()
            -> getOneOrNullResult();
    }

    /**
     * @param $usernameOrEmail
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        if (preg_match('/^.+\@\S+\.\S+$/', $usernameOrEmail)) {
            $user = $this->findUserByEmail($usernameOrEmail);
            if (null !== $user) {
                return $user;
            }
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findUserByConfirmationToken($token)
    {
        return $this->createQueryBuilder('u')
            -> andWhere('u.confirmationToken =:token')
            -> setParameter('token', $token)
            -> getQuery()
            -> getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findCustomerAndProfilUser($id)
    {
        return $this->createQueryBuilder('u')
            -> andWhere('u.id =:id')
            -> setParameter('id', $id)
            -> leftJoin('u.customer', 'c')
            -> addSelect('c')
            -> leftJoin('c.profil', 'p')
            -> addSelect('p')
            -> getQuery()
            -> getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */


}
