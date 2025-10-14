<?php

namespace App\Controller\MainPublic;

use App\Classe\UserSessionTrait;
use App\Lib\Links;
use App\Service\Search\SearchRessources;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class RessourcesPublicController extends AbstractController
{

    use UserSessionTrait;

    #[Route('/ressources', name:"ressources")]
    public function allRessources(Request $request, SearchRessources $searchRessources): Response
    {
       $vartwig=$this->menuNav->templatepotins(
           'list',
           Links::RESSOURCES,
          );

        $keyword = trim((string) $request->query->get('q', ''));
        $categoryId = $request->query->getInt('categorie', 0) ?: null;

        $ressources = $searchRessources->searchPublic($keyword !== '' ? $keyword : null, $categoryId);

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'ressources',
            'replacejs'=>false,
            'ressources'=>$ressources,
            'categories' => $searchRessources->getCategories(),
            'filters' => [
                'q' => $keyword,
                'categorie' => $categoryId,
            ],
            'vartwig'=>$vartwig,
            'customer' => $this->customer,
        ]);
    }

    #[Route('/ressources/infos', name:"ressources_info")]
    public function ressourcesInfo(SearchRessources $searchRessources): Response
    {
        $vartwig=$this->menuNav->templatepotins(
            'ressources',
            Links::RESSOURCES,
        );

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory' => 'ressources',
            'replacejs' => false,
            'categories' => $searchRessources->getCategories(),
            'vartwig' => $vartwig,
            'customer' => $this->customer,
        ]);
    }

    #[Route('show-ressource/{id}', name:"show_ressource")]
    public function showRessource(SearchRessources $searchRessources, $id): Response
    {

        if(!$ressource=$searchRessources->searchOneRsscWithOtherRsscCat($id))return $this->redirectToRoute('board_all');

        $vartwig=$this->menuNav->templatepotins(
            'showressource',
            Links::RESSOURCES);


        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'ressources',
            'replacejs'=>!empty($tab['posts']),
            'vartwig' => $vartwig,
            'ressource'=>$ressource['rssc'],
            'ressources'=>$ressource['rsscs'],
            'content'=>$ressource['content'],
            'otherressources'=>[],
            'customer' => $this->customer,
        ]);
    }

}
