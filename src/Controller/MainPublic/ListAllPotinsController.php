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
           'lastpotins',
           Links::POTINS);

        $lastnotices=$listpublications->listAllPotins();

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'board',
            'replacejs'=>!empty($lastnotices),
            'vartwig'=>$vartwig,
            'lastsnotice'=>$lastnotices,
        ]);
    }

    #[Route('/les-prochains-potins', name:"events_all")]
    public function allEvents(Searchmodule $searchmodule): Response
    {
        $vartwig=$this->menuNav->templatepotins(
            'eventboard',
            Links::EVENT);


        $tab=$searchmodule->findLastBeforeWeek();
        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'board',
            'replacejs'=>!empty($lastnotices),
            'vartwig'=>$vartwig,
            'events'=>$tab,
        ]);
    }
}
