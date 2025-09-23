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
            'index',
            Links::CNFS);

        return $this->render($this->useragentP.'ptn_cnfs/home.html.twig', [
            'directory'=>'cnfs',
            'replacejs'=>false,
            'vartwig'=>$vartwig,
        ]);
    }

    #[Route('/cartographie', name:"carto_cnfs")]
    public function cartoCnfs(): Response
    {
       $vartwig=$this->menuNav->templatepotins(
           'cartographie',
           Links::CNFS);

        return $this->render($this->useragentP.'ptn_cnfs/home.html.twig', [
            'directory'=>'cnfs',
            'vartwig'=>$vartwig,
        ]);
    }

    #[Route('/ask-conseiller-numerique', name:"ask_cnfs")]
    public function askCnfs(): Response
    {

        $vartwig=$this->menuNav->templatepotins(
            'ask',
            Links::CNFS);

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'cnfs',
            'replacejs'=>false,
            'vartwig' => $vartwig,
        ]);
    }

}
