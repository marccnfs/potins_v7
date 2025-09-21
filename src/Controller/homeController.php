<?php

namespace App\Controller;

use App\Entity\Users\User;
use App\Lib\Links;
use App\Repository\BoardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class homeController extends AbstractController
{
    #[Route('potins/{user}', name:"potins")]
    public function index(BoardRepository $boardRepository, String $user): Response
    {
        //$medias=$boardRepository->findMedia(); // les mediathèques

        $vartwig=[
            'twig'=>'indexpublic',
            'title'=>'test',
            'description'=>'ras',
            'tagueries'=>'test security',
            'linkbar'=>['0','1','2','3','4','5'],
            'maintwig'=>'indexpublic'
        ];

        return $this->render('desk/ptn_public/home.html.twig', [
            'directory'=>'main',
            'replacejs'=>false,
            'medias'=>null,
            'vartwig'=>$vartwig,
            'user'=>$user
        ]);
    }
}
