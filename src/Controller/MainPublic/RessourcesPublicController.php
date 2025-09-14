<?php

namespace App\Controller\MainPublic;

use App\Classe\PublicSession;
use App\Lib\Links;
use App\Service\Search\SearchRessources;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class RessourcesPublicController extends AbstractController
{

 use PublicSession;

    #[Route('/ressources', name:"ressources")]
    public function allRessources(SearchRessources $searchRessources): Response
    {
       $vartwig=$this->menuNav->templatepotins(
           Links::PUBLIC,
           'list',
           3,
           "");

        $ressources=$searchRessources->findAllCartes();

       // todo recuperer l'article pour transmettre la date de création
        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'ressources',
            'replacejs'=>false,
            'ressources'=>$ressources,
            'vartwig'=>$vartwig,
            'member'=>$this->member,
            'customer'=>$this->customer,
        ]);
    }

    #[Route('show-ressource/{id}', name:"show_ressource")]
    public function showRessource(SearchRessources $searchRessources, $id): Response
    {

        if(!$ressource=$searchRessources->searchOneRsscWithOtherRsscCat($id))return $this->redirectToRoute('board_all');

        $vartwig=$this->menuNav->templatepotins(
            Links::PUBLIC,
            'showressource',
            3,
            "nocity");


        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'ressources',
            'replacejs'=>!empty($tab['posts']),
            'vartwig' => $vartwig,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'ressource'=>$ressource['rssc'],
            'ressources'=>$ressource['rsscs'],
            'content'=>$ressource['content'],
            'otherressources'=>[],
        ]);
    }

}
