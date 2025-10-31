<?php

namespace App\Controller\MainPublic;

use App\Classe\PublicSession;
use App\Lib\Links;
use App\Service\ClientApiIaTools;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PublicAiToolController extends AbstractController
{

    use PublicSession;

    #[Route('/ai-toolsOld', name:"ai_tools_liste")]
    public function listeAiTools(ClientApiIaTools $clientApi): Response
    {
        $aiTools = $clientApi->getAiTools();
        $toolsList = $aiTools['member'] ?? [];


        $vartwig=$this->menuNav->templatepotins(
            Links::PUBLIC,
            'listnews',
            5,
            "");

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'news',
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'ai_tools' =>$toolsList
        ]);

    }

}
