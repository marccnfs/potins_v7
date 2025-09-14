<?php


namespace App\Service\backoffice;


use App\Repository\Entity\FacturesRepository;


class Initbacktor
{

    private FacturesRepository $facturesRepository;


    public function __construct(FacturesRepository $facturesRepository)
    {

        $this->facturesRepository = $facturesRepository;
    }

    public function init(): array
    {

        return $this->facturesRepository->findAll();
    }
}