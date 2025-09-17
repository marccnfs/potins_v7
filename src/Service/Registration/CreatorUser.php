<?php

namespace App\Service\Registration;

use App\Entity\Admin\NumClients;
use App\Entity\Admin\Numeratum;
use App\Entity\ApiToken;
use App\Entity\Customer\Customers;
use App\Entity\Customer\Services;
use App\Entity\UserMap\Heuristiques;
use App\Entity\Users\ProfilUser;
use App\Entity\Users\User;
use App\Heuristique\Synapse;
use App\Repository\ProductsRepository;
use App\Util\Canonicalizer;
use App\Util\DefaultModules;
use App\Util\PasswordUpdater;
use LogicException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;


class CreatorUser
{

    private PasswordUpdater $passwordUpdater;
    private Canonicalizer $canonicalizer;
    private EntityManagerInterface $em;
    private ProductsRepository $producRepro;

    public function __construct(ProductsRepository $productsRepository, PasswordUpdater $passwordUpdater,Canonicalizer $canonicalizer, EntityManagerInterface $em)
    {
        $this->passwordUpdater=$passwordUpdater;
        $this->canonicalizer=$canonicalizer;
        $this->em=$em;
        $this->producRepro=$productsRepository;
    }


    /**
     * @throws NonUniqueResultException
     */
    public function inituser(User $user, $form,  Numeratum $nums): Customers
    {
        $stringpass=$form['plainPassword']->getData() ?? "";
        $mail=$form['email']->getData() ?? "";
        return $this->newCompte($user, $nums,false,$stringpass,$mail);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function initmember(User $user, $form, Numeratum $nums): Customers
    {
        $stringpass=$form['plainPassword']->getData() ?? "";
        $mail=$form['email']->getData() ?? "";
        return $this->newCompte($user, $nums,true,$stringpass,$mail);
    }

    public function initMediatheque(User $user, $form, Numeratum $nums): Customers
    {
        $stringpass=$form['plainPassword']->getData() ?? "";
        $mail=$form['email']->getData() ?? "";
        return $this->newCompteMediatheque($user, $nums,true,$stringpass,$mail);
    }

    public function createContactByMedia($form,Numeratum $nums): Customers
    {
            $user=New User();
            $apitoken = New ApiToken($user);
            $customer=new Customers();
            $customer->setIsMember(false);
            $user->setCustomer($customer);
            $numeroclient=New NumClients();
            $identity = new ProfilUser();
            $identity->setFirstname($form['name']->getData()??"");
            $identity->setSex($form['sexe']->getData()??null);
            $identity->setTelephonemobile($form['telephone']->getData()??"");
            $identity->setMdpfirst(bin2hex(random_bytes(5)));
            $identity->setEmailfirst($form['email']->getData());
            $customer->setProfil($identity);
            $user->addRole("ROLE_USAGER");
            $user->setDatemajAt(new \DateTime());
            $user->setEmail($form['email']->getData());
            $this->passwordUpdater->hashPasswordstring($user, $identity->getMdpfirst());
            $this->assertPasswordHasBeenHashed($user);
            $this->canonicalizer->updateCanonicalFields($user);
            $numeroclient->setNumero($nums->getNumClient());
            $numeroclient->setOrdre(date("Y"));
            $numeroclient->setIdcustomer($customer);
            $customer->setClient(true);
            $customer->setEmailcontact($user->getEmailCanonical());
            $customer->setNumclient($numeroclient);
            $heuristique = new Heuristiques($customer);
            $sys=Synapse::INSCRIPTION;
            $heuristique->setSem($sys[0]);
            $heuristique->setColor($sys[1]);
            $heuristique->setBinarycolor($sys[2]);
            $this->em->persist($heuristique);
            $this->em->persist($apitoken);
            $this->em->persist($numeroclient);
            $this->em->persist($customer);
            return $customer;
    }

    public function newCompte(User $user, Numeratum $nums, $ismember, string $stringpass, $mail): Customers
    {
        $apitoken = New ApiToken($user);
        $customer=new Customers();
        $customer->setIsMember($ismember);
        $user->setCustomer($customer);
        $numeroclient=New NumClients();

        foreach (DefaultModules::MODULE_LIST as $list){
            $rpo=$this->producRepro->findOneProduct($list);
            if($rpo){
                $service= new Services();
                $service->setNamemodule($list);
                $service->setProducts($rpo);
                $service->setDatestartAt(new DateTime());
                $customer->addService($service);
                $this->em->persist($service);
            }
        }

        $identity = new ProfilUser();
        if(!$stringpass){
            $identity->setMdpfirst(bin2hex(random_bytes(5)));
        }else{
            $identity->setMdpfirst($stringpass);
        }
        $identity->setEmailfirst($mail);
        $customer->setProfil($identity);
        $user->addRole($ismember?"ROLE_MEMBER":"ROLE_CUSTOMER");
        $user->setDatemajAt(new \DateTime());
        $user->setEmail($mail);

        $this->passwordUpdater->hashPasswordstring($user, $identity->getMdpfirst());
        $this->assertPasswordHasBeenHashed($user);
        $this->canonicalizer->updateCanonicalFields($user);

        $numeroclient->setNumero($nums->getNumClient());
        $numeroclient->setOrdre(date("Y"));
        $numeroclient->setIdcustomer($customer);

        $customer->setClient(true);
        $customer->setEmailcontact($user->getEmailCanonical());
        $customer->setNumclient($numeroclient);

        $heuristique = new Heuristiques($customer);
        $sys=Synapse::INSCRIPTION;
        $heuristique->setSem($sys[0]);
        $heuristique->setColor($sys[1]);
        $heuristique->setBinarycolor($sys[2]);

        $this->em->persist($heuristique);
        $this->em->persist($apitoken);
        $this->em->persist($numeroclient);
        $this->em->persist($customer);
        return $customer;
    }

    public  function modifCustomer($customer): bool
    {
        $this->em->persist($customer);
        $this->em->flush();
        return true;
    }

    public function newCompteMediatheque(User $user, Numeratum $nums, $ismember, string $stringpass, $mail): Customers
    {
        $apitoken = New ApiToken($user);
        $customer=new Customers();
        $customer->setIsMember($ismember);
        $user->setCustomer($customer);
        $numeroclient=New NumClients();

        foreach (DefaultModules::MODULE_LIST as $list){
            $rpo=$this->producRepro->findOneProduct($list);
            if($rpo){
                $service= new Services();
                $service->setNamemodule($list);
                $service->setProducts($rpo);
                $service->setDatestartAt(new DateTime());
                $customer->addService($service);
                $this->em->persist($service);
            }
        }

        $identity = new ProfilUser();
        if(!$stringpass){
            $identity->setMdpfirst(bin2hex(random_bytes(5)));
        }else{
            $identity->setMdpfirst($stringpass);
        }
        $identity->setEmailfirst($mail);
        $customer->setProfil($identity);
        $user->addRole("ROLE_MEDIA");
        $user->setDatemajAt(new \DateTime());
        $user->setEmail($mail);

        $this->passwordUpdater->hashPasswordstring($user, $identity->getMdpfirst());
        $this->assertPasswordHasBeenHashed($user);
        $this->canonicalizer->updateCanonicalFields($user);

        $numeroclient->setNumero($nums->getNumClient());
        $numeroclient->setOrdre(date("Y"));
        $numeroclient->setIdcustomer($customer);

        $customer->setClient(true);
        $customer->setEmailcontact($user->getEmailCanonical());
        $customer->setNumclient($numeroclient);

        $heuristique = new Heuristiques($customer);
        $sys=Synapse::INSCRIPTION;
        $heuristique->setSem($sys[0]);
        $heuristique->setColor($sys[1]);
        $heuristique->setBinarycolor($sys[2]);

        $this->em->persist($heuristique);
        $this->em->persist($apitoken);
        $this->em->persist($numeroclient);
        $this->em->persist($customer);
        return $customer;
    }

    private function assertPasswordHasBeenHashed(User $user): void
    {
        try {
            $password = $user->getPassword();
        } catch (\TypeError $exception) {
            throw new LogicException('Password hashing failed for the new account.', 0, $exception);
        }

        if ($password === null) {
            throw new LogicException('Password hashing failed for the new account.');
        }
    }


}
