<?php


namespace App\Service\Gestion;

use App\Entity\Admin\Factures;
use App\Entity\Admin\FacturesCustomer;
use App\Entity\Admin\Orders;
use App\Entity\Admin\Wborders;
use App\Entity\Member\Activmember;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;


class Facturator
{

    private Numerator $numerator;
    private EntityManagerInterface $em;
    private GetFacture $getFacture;
    private Commandar $commandar;

    /**
     * Facturator constructor.
     * @param EntityManagerInterface $em
     * @param Numerator $numerator
     * @param GetFacture $getFacture
     * @param Commandar $commandar
     */
    public function __construct(EntityManagerInterface $em,  Numerator $numerator, GetFacture $getFacture, Commandar $commandar)
    {
        $this->em = $em;
        $this->numerator = $numerator;
        $this->getFacture = $getFacture;
        $this->commandar = $commandar;

    }

    /**
     * @param Wborders $order
     * @param Activmember $dispatch
     */
    public function newFacture(Wborders $order,Activmember $dispatch): Dompdf
    {
        $this->commandar->initfacture($order);
        $facture =new Factures();
        $facture->setCreateAt($order->getModifdate() ? $order->getModifdate() : $order->getDate());
        $facture->setNumfact(($this->numerator->getFactNumerate())->getNumFact());
        $facture->setOrders($order);
        $facture->setMontantttc($order->getTotalttc());
        $dompdf=$this->getFacture->newpdffacture($facture, $order, $dispatch);
        $this->em->persist($facture);
        $this->em->flush();
        return $dompdf;
    }

    public function replaceFacture($order,Activmember $dispatch, $facture): Dompdf
    {
        $dompdf=$this->getFacture->miseAjPdffacture($facture, $order, $dispatch);
        $this->em->persist($facture);
        $this->em->flush();
        return $dompdf;
    }

    /**
     * @param Orders $order
     * @return Dompdf
     */
    public function newFactureCustomer(Orders $order): Dompdf
    {
        $customer=$order->getNumclient()->getIdcustomer();
        $dispatch=$customer->getMember();
        $this->commandar->initfactureCustomer($order);
        $facture =new FacturesCustomer();
        $facture->setCreateAt($order->getModifdate() ? $order->getModifdate() : $order->getDate());
        $facture->setNumfact(($this->numerator->getFactNumerate())->getNumFact());
        $facture->setOrders($order);
        $facture->setMontantttc($order->getTotalttc());
        $dompdf=$this->getFacture->newpdffactureCustomer($facture, $order, $dispatch);
        $this->em->persist($facture);
        $this->em->flush();
        return $dompdf;
    }

    public function replaceFactureCustomer($order,Activmember $dispatch, $facture): Dompdf
    {
        $dompdf=$this->getFacture->miseAjPdffacture($facture, $order, $dispatch);
        $this->em->persist($facture);
        $this->em->flush();
        return $dompdf;
    }
}