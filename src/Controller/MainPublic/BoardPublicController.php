<?php

namespace App\Controller\MainPublic;


use App\Classe\PublicSession;
use App\Lib\Calopen;
use App\Lib\Links;
use App\Repository\BoardRepository;
use App\Repository\SectorsRepository;
use App\Service\Search\Listpublications;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;


class BoardPublicController extends AbstractController
{

 use PublicSession;

    /*
     * affiche la page synthese publique d'un potin
     */

    #[Route('/info-potins/{slugboard}', name:'show_board', defaults:['pg:0'])]
    public function infoPotin(SectorsRepository $reposector, $slugboard, Listpublications $listpublications, BoardRepository $websiteRepository,  Calopen $calopen,EventDispatcherInterface $dispatcher): Response
    {
        $bulles=false;

        $this->board=$websiteRepository->findWbBySlug($slugboard);
        if(!isset($this->board))throw new Exception('potins inconnu');
        $listmodule=$this->board->getListmodules();
        $notices=$listpublications->listPublicationsAndModules($this->board, $listmodule);
        $noticesall=$listpublications->listPublicationsboard($this->board);
        $sector=$reposector->findWithAdressByCodesite($this->board->getCodesite());

        if ($this->member) {
            $vartwig=$this->menuNav->templatepotinsBoard(
                Links::PUBLIC,
                'showWb',
                0,
                $this->board,
                "nocity"
            );
            $follow=false;
        }else {
            $follow=true;
            $vartwig = $this->menuNav->websiteinfoObj($this->board, 'showWb', $this->board->getNameboard(), 'visitor');
        }

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'board',
            'member'=>$this->member,
            'customer'=>$this->customer,
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'isfollow'=>$follow,
            'notices'=>$notices,
            'noticesall'=>$noticesall,
            'board'=>$this->board,
            'infowb'=>true,
            'pw'=>$this->pw??false,
            'modules'=>$listmodule,
            'sector'=>$sector,
            'openday'=>$this->board->getTabopendays()? $calopen->cal($this->board->getTabopendays()):"",
        ]);
    }



    /*
     * affiche le panneau d'un potin
     */

    #[Route('board/{slugboard}', name:"board_potin_public")]
    public function showBoard(Listpublications $listpublications,  BoardRepository $boardRepository, $slugboard): Response
    {
        $this->board=$boardRepository->findWbBySlug($slugboard);
        if(!isset($this->board))return $this->redirectToRoute('potins_index'); //todo adpater mieux
        $notices=$listpublications->listPublicationsboard($this->board);

        $vartwig=$this->menuNav->websiteinfoObj(
            $this->board,
            'showboard',
            0,
            'visitor');

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'board',
            'replacejs'=>!empty($notices),
            'vartwig' => $vartwig,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'notices'=>$notices,
            'pw'=>$this->pw??false,
            'board'=>$this->board,
            'locatecity'=>0,
        ]);
    }
}
