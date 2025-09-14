<?php


namespace App\Controller\Cnfs;

use App\Classe\potinsession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class CnfsBlogController extends AbstractController
{
    use potinsession;


    #[Route('/nice1', name:'nice1')]
    public function nice1()
    {
        $vartwig=$this->navigator->dispatchinfo('nice1');
        return $this->render('contenus/pages/pg-ressources.html.twig', [
            'vartwig'=>$vartwig,
            'agent'=>$this->useragent,
            'content' => 'inc-section-nice1.html.twig',
            'page'=>'nice1'
        ]);
    }

    #[Route('/nice2', name:'nice2')]
    public function nice2()
    {
        $vartwig=$this->navigator->dispatchinfo('nice1');
        return $this->render('contenus/pages/pg-ressources.html.twig', [
            'vartwig'=>$vartwig,
            'agent'=>$this->useragent,
            'content' => 'inc-section-nice2.html.twig',
            'page'=>'nice2'
        ]);
    }

}