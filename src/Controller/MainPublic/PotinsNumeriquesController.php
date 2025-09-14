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
        $medias=$boardRepository->findMedia(); // les mediathèques

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            'indexpublic',
            0,
            "nocity");

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'main',
            'replacejs'=>false,
            'medias'=>$medias,
            'customer'=>$this->customer,
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
            Links::ACCUEIL,
            'pacman',
            0,
            "nocity");

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'games',
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('/agenda', name:"agenda")]  // n'est pas en fonction pas de front
    public function agenda(): Response
    {
        $vartwig=$this->menuNav->templatepotins(
            Links::AGENDA,
            'agenda',
            0,
            "nocity");

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'main',
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('/decouvrez', name:"decouverte")]
    public function pgToFind(Searchmodule $searchmodule): Response
    {

        $vartwig=$this->menuNav->templatepotins(
            Links::DECOUV,
            'decouverte',
            0,
            "nocity");

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'main',
            'posts'=>$potins,
            'customer'=>$this->customer,
            'replacejs'=>!empty($posts),
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('/apprendre', name:"learn_info")]
    public function pgToLearn(): Response
    {
        $vartwig=$this->menuNav->templatepotins(
            Links::LEARN,
            'tolearn',
            0,
            "nocity");

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'main',
            'customer'=>$this->customer,
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
            Links::TECHNO,
            'rdv_samedi',
            0,
            "nocity");

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'main',
            'customer'=>$this->customer,
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
            Links::RESSOURCES,
            'ressources',
            0,
            "nocity");

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'main',
            'customer'=>$this->customer,
            'replacejs'=>false,
            'vartwig'=>$vartwig
        ]);
    }

}
