<?php

namespace App\Service\Gestion;

use App\Entity\Admin\Numeratum;
use App\Repository\NumeratumRepository;
use Doctrine\ORM\EntityManagerInterface;

class Numerator
{

    private NumeratumRepository $nuratumRepository;
    private EntityManagerInterface $em;

    /**
     * Numerator constructor.
     * @param NumeratumRepository $nuratumRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(NumeratumRepository $nuratumRepository, EntityManagerInterface $em)
    {
        $this->nuratumRepository = $nuratumRepository;
        $this->em = $em;
    }

    /**
     * @return Numeratum|null
     */
    public function getActiveNumerate(): ?Numeratum
    {

        $num = $this->nuratumRepository->find(1);
        if (!$num){
            $num=new Numeratum(1);
            $this->em->persist($num);
        }else{
            $temp['cmd']=$num->getNumCmd();
            $num->setNumCmd(++$temp['cmd']);
            $temp['cli']=$num->getNumClient();
            $num->setNumClient(++$temp['cli']);
        }
        $this->em->flush();
        return $num;
    }

    public function getActiveNumeratecustomer(): ?Numeratum
    {
        $num = $this->nuratumRepository->find(1);
        if (!$num){
            $num=new Numeratum(1);
            $this->em->persist($num);
        }else{
            $temp['cli']=$num->getNumClient();
            $num->setNumClient(++$temp['cli']);
        }
        $this->em->flush();
        return $num;
    }

    public function getActiveNumeratewebsite(): ?Numeratum
    {
        $num = $this->nuratumRepository->find(1);
        if (!$num){
            $num=new Numeratum(1);
            $this->em->persist($num);
        }else{
            $temp['cli']=$num->getNumClient();
            $num->setNumWebsite(++$temp['cli']);
        }
        $this->em->flush();
        return $num;
    }

    /**
     * @return Numeratum|null
     */
    public function getCmdNumerate(): ?Numeratum
    {
        $num = $this->nuratumRepository->find(1);
        $temp=$num->getNumCmd();
        $num->setNumCmd(++$temp);
        $this->em->flush();
        return $num;
    }

    /**
     * @return Numeratum|null
     */
    public function getFactNumerate(): ?Numeratum
    {
        $num = $this->nuratumRepository->find(1);
        $temp=$num->getNumFact();
        $num->setNumFact(++$temp);
        $this->em->flush();
        return $num;
    }


}