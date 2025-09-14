<?php


namespace App\Service\Gestion;


use App\Entity\Admin\NumClients;
use App\Entity\Admin\OrderProducts;
use App\Entity\Admin\Orders;
use App\Entity\UserMap\Heuristiques;
use App\Heuristique\Synapse;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;


class AutoCommande
{

    private ProductsRepository $productsRepository;
    private Numerator $numerator;
    private EntityManagerInterface $em;
    private Commandar $commandar;

    public function __construct(EntityManagerInterface $em,Numerator $numerator, ProductsRepository $productsRepository, Commandar $commandar)
    {
        $this->em = $em;
        $this->productsRepository = $productsRepository;
        $this->commandar = $commandar;
        $this->numerator=$numerator;
    }

    /**
     * @param NumClients $client
     * @return bool
     * @throws NonUniqueResultException
     */
    public function newInscriptionCmd(NumClients $client): bool
    {
        $customer=$client->getIdcustomer();
        $order=new Orders();
        $cmd=new OrderProducts();
        $cmd->setProduct($this->productsRepository->findOneProduct("activation"));
        $cmd->setMultiple(1);
        $cmd->setOrder($order);
        $order->setNumcommande(($this->numerator->getCmdNumerate())->getNumCmd());
        $client->addOrder($order);
        $heuristique = new Heuristiques($customer);
        $sys = Synapse::ACTIVATION;
        $heuristique->setSem($sys[0]);
        $heuristique->setColor($sys[1]);
        $heuristique->setBinarycolor($sys[2]);
        $this->commandar->calOrderproduct($cmd, $order);
        $this->em->persist($order);
        $this->em->persist($cmd);
        $this->em->persist($client);
        $this->em->persist($heuristique);
        $this->em->flush();

        return true;

    }

    /**
     * @param NumClients $client
     * @param Orders $order
     * @param $module
     * @return bool
     * @throws NonUniqueResultException
     */
    public function newCmdModule(NumClients $client, Orders $order, $module): bool
    {
        $cmd=new OrderProducts();
        $cmd->setProduct($this->productsRepository->findOneProduct($module));
        $cmd->setMultiple(1);
        $cmd->setOrder($order);
        $order->setNumcommande(($this->numerator->getCmdNumerate())->getNumCmd());
        $client->addOrder($order);
        $this->commandar->calOrderproduct($cmd, $order);
        $this->em->persist($order);
        $this->em->persist($cmd);
        $this->em->persist($client);
        $this->em->flush();

        return true;
    }
}