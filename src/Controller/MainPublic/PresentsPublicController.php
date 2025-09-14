<?php

namespace App\Controller\MainPublic;

use App\Classe\PublicSession;
use App\Lib\Links;
use App\Repository\OffresRepository;
use App\Service\Search\SearchPresent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class PresentsPublicController extends AbstractController
{

 use PublicSession;

    #[Route('/presents/happy-chrismes/{codepersonnel}', name:"presents_happy_secret")]
    public function showPresent(OffresRepository $offresRepository,SearchPresent $searchPresent,$codepersonnel): Response
    {

        $tab=$searchPresent->searchOneRsscWithOtherRsscCat($codepersonnel);

       $vartwig=$this->menuNav->templatepotins(
           Links::PUBLIC,
           'showpresent_nopotin',
           3,
           "");

        return $this->render('nopotin/home.html.twig', [
            'directory'=>'offre',
            'replacejs'=>false,
            'tab'=>$tab,
            'vartwig'=>$vartwig,
            'member'=>$this->member,
            'customer'=>$this->customer,
        ]);
    }
}
