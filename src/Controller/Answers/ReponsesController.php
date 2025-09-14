<?php

namespace App\Controller\Answers;

use App\Classe\potinsession;
use App\Entity\Boards\Board;
use App\Lib\Links;
use App\Repository\BoardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;



#[Route('/notifi')]

class ReponsesController extends AbstractController
{

 use potinsession;


    #[Route('/bienvenue/{slug}/{type}/{id}', name:"contact_keep")]
    public function kepping(Request $request, BoardRepository $websiteRepository,$slug=null,$id=null,$type=null): Response
    {
        /** @var Board $website */
        $website=$websiteRepository->findWbBySlug($slug);
       $locate=$website->getLocality();
       $city=$locate->getSlugcity();

        $vartwig=$this->menuNav->templateControl(
            Links::CUSTOMER_LIST,
            'keep',
            "keep",
            'all');

        return $this->render('public/home.html.twig', [
            'locate'=>$locate,
            'replacejs'=>false,
            'city'=>$city,
            'vartwig'=>$vartwig,
            'website'=>$website,
            'directory'=>'page',
            'admin'=>[$this->admin,$this->permission]
        ]);
    }




}
