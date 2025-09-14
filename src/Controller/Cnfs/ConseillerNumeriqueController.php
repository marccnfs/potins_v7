<?php

namespace App\Controller\Cnfs;

use App\Classe\PublicSession;
use App\Lib\Links;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ConseillerNumeriqueController extends AbstractController
{

 use PublicSession;

    #[Route('/conseiller-numerique', name:"cnfs")]
    public function cnfs(): Response
    {
        $vartwig=$this->menuNav->templatepotins(
            Links::PUBLIC,
            'index',
            2,
            "");
        $post=['id'=>1];

        return $this->render($this->useragentP.'ptn_cnfs/home.html.twig', [
            'directory'=>'cnfs',
            'replacejs'=>false,
            'board'=>$this->board,
            'vartwig'=>$vartwig,
            'post'=>$post,
            'member'=>$this->member,
            'customer'=>$this->customer,
        ]);
    }

    #[Route('/cartographie', name:"carto_cnfs")]
    public function cartoCnfs(): Response
    {
       $vartwig=$this->menuNav->templatepotins(
           Links::PUBLIC,
           'cartographie',
           3,
           "");

        return $this->render($this->useragentP.'ptn_cnfs/home.html.twig', [
            'directory'=>'cnfs',
            'board'=>$this->board,
            'vartwig'=>$vartwig,
            'member'=>$this->member,
            'customer'=>$this->customer,
        ]);
    }

    #[Route('/ask-conseiller-numerique', name:"ask_cnfs")]
    public function askCnfs(): Response
    {

        $vartwig=$this->menuNav->templatepotins(
            Links::PUBLIC,
            'ask',
            4,
            "nocity");


        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'cnfs',
            'replacejs'=>false,
            'vartwig' => $vartwig,
            'member'=>$this->member,
            'customer'=>$this->customer,
        ]);
    }

}
