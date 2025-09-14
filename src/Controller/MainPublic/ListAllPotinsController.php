<?php

namespace App\Controller\MainPublic;

use App\Classe\PublicSession;
use App\Lib\Links;
use App\Service\Search\Listpublications;
use App\Service\Search\Searchmodule;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ListAllPotinsController extends AbstractController
{

    use PublicSession;

    #[Route('/les-derniers-potins', name:"board_all")]
    public function allPotins(Listpublications $listpublications): Response
    {
       $vartwig=$this->menuNav->templatepotins(
           Links::POTINS,
           'lastpotins',
           1,
           "nocity");

        $lastnotices=$listpublications->listAllPotins();
      //  dump($lastnotices);

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'board',
            'replacejs'=>!empty($lastnotices),
            'vartwig'=>$vartwig,
            'customer'=>$this->customer,
            'member'=>$this->member,
            'lastsnotice'=>$lastnotices,
        ]);
    }

    #[Route('/les-prochains-potins', name:"events_all")]
    public function allEvents(Searchmodule $searchmodule): Response
    {
        $vartwig=$this->menuNav->templatepotins(
            Links::EVENT,
            'eventboard',
            1,
            "nocity");


        $tab=$searchmodule->findLastBeforeWeek();
        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'board',
            'replacejs'=>!empty($lastnotices),
            'vartwig'=>$vartwig,
            'events'=>$tab,
            'board'=>$this->board??[],
            'member'=>$this->member,
            'customer'=>$this->customer,
        ]);
    }
}
