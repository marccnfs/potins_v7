<?php


namespace App\Controller\Page;

use App\Classe\sessionConnect;
use App\Lib\Links;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


#[Route('/information-affi/to-use/')]

class pageInfoController extends AbstractController
{
    use sessionConnect;



    #[Route('conditions-generale-utilisation', name:"cgu_affi")]
    public function goInAffichange()
    {
        if($this->iddispatch) return $this->redirectToRoute('cargo_public');

        $vartwig=$this->menuNav->templateControl(
            Links::PUBLIC,
            'charte',
            "cgu",
            'all');

        return $this->render('public/home.html.twig', [
            'city'=>null,
            'directory'=>"page",
            'vartwig'=>$vartwig,

        ]);

    }

    #[Route('conditions-generales-utilisation', name:"cgu")]
    public function apropos(): Response
    {
        $vartwig=$this->menuNav->templateControl(
            Links::PUBLIC,
            "pagecgu",
            'c-g-u',
            'all'
        );

        return $this->render('aff_public/home.html.twig', [
            'city'=>null,
            'directory'=>"page",
            'dispatch'=>$this->dispatch??[],
            'replacejs'=>$replacejs??null,
            'vartwig'=>$vartwig,
        ]);
    }


    #[Route('le-site/confidentialite', name:"confidentialite_affi")]
    public function confidentialite(): Response
    {
        $vartwig=$this->menuNav->templateControl(
            Links::PUBLIC,
            "confidentialite",
            'confidentialite',
            'all'
        );

        return $this->render('public/home.html.twig', [
            'city'=>null,
            'directory'=>"page",
            'vartwig'=>$vartwig,
        ]);
    }




}