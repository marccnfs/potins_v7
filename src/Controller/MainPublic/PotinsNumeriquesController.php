<?php

namespace App\Controller\MainPublic;

use App\Classe\PublicSession;
use App\Lib\Links;
use App\Repository\BoardRepository;
use App\Service\Search\Searchmodule;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class PotinsNumeriquesController extends AbstractController
{

use PublicSession;

    #[Route('', name:"potins_index")]
    public function index(BoardRepository $boardRepository): Response
    {
        $medias=$boardRepository->findMedia();
        $vartwig=$this->menuNav->templatepotins('indexpublic', Links::ACCUEIL,);
        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'main',
            'replacejs'=>false,
            'medias'=>$medias,
            'vartwig'=>$vartwig
        ]);
    }

/* maintenance

    #[Route('', name:"potins_index")]
    public function maintenance(BoardRepository $boardRepository): Response
    {
        $medias=$boardRepository->findMedia();

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            'indexpublic',
            0,
            "nocity");

        return $this->render($this->useragentP.'ptn_maintenance/home.html.twig', [
            'directory'=>'maintenance',
            'replacejs'=>false,
            'medias'=>$medias,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig
        ]);
    }

*/

    #[Route('pacman', name:"potins_pacman")]
    public function pacmanGame(): Response
    {
        $vartwig=$this->menuNav->templatepotins(
            'pacman',
            Links::ACCUEIL);

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'games',
            'replacejs'=>false,
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('/agenda', name:"agenda")]  // n'est pas en fonction pas de front
    public function agenda(): Response
    {
        $vartwig=$this->menuNav->templatepotins(
            'agenda',
            Links::AGENDA);

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'main',
            'replacejs'=>false,
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('/decouvrez', name:"decouverte")]
    public function pgToFind(Searchmodule $searchmodule): Response
    {

        $vartwig=$this->menuNav->templatepotins(
            'decouverte',
            Links::DECOUV);

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'main',
            'replacejs'=>!empty($posts),
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('/apprendre', name:"learn_info")]
    public function pgToLearn(): Response
    {
        $vartwig=$this->menuNav->templatepotins(
            'tolearn',
            Links::LEARN);

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'main',
            'replacejs'=>false,
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('/les-rendez-vous-du-samedi', name:"rdv_samedi")]
    public function pgRendezVousDuSamedi(Searchmodule $searchmodule, BoardRepository $boardRepository): Response
    {

        $potins=$searchmodule->searchAllPotinsOther();
        $medias=$boardRepository->findMedia(); // les mediathèques

        $vartwig=$this->menuNav->templatepotins(
            'rdv_samedi',
            Links::TECHNO);

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'main',
            'potins'=>$potins,
            'medias'=>$medias,
            'replacejs'=>false,
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('/les-ressources', name:"ressources_info")]
    public function pgRessources(): Response
    {
        $vartwig=$this->menuNav->templatepotins(
            'ressources',
            Links::RESSOURCES);

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'main',
            'replacejs'=>false,
            'vartwig'=>$vartwig
        ]);
    }

}
