<?php


namespace App\Controller\Cnfs;

use App\Classe\potinsession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class CnfsController extends AbstractController
{
    use potinsession;

    #[Route('/cnfs', name:'cnfs')]
    public function cnfs()
    {
        $vartwig=$this->navigator->dispatchinfo('cnfs');
        return $this->render('contenus/pages/pg-cnfs.html.twig', [
            'vartwig'=>$vartwig,
            'agent'=>$this->useragent,
            'content' => 'inc-section-cnfs.html.twig',
            'page'=>'cnfs'
        ]);
    }

    #[Route('/accompagnement', name:"accompagnement")]
    public function accompagnement()
    {
        $vartwig=$this->navigator->dispatchinfo('accompagnement');
        return $this->render('contenus/pages/pg-accompagnement.html.twig', [
            'vartwig'=>$vartwig,
            'agent'=>$this->useragent,
            'content' => 'inc-section-accompagnement.html.twig',
            'page'=>'accompagnement'
        ]);
    }

    #[Route('/ateliers', name:"atelier")]
    public function atelier()
    {
        $vartwig=$this->navigator->dispatchinfo('atelier');
        return $this->render('contenus/pages/pg-atelier.html.twig', [
            'vartwig'=>$vartwig,
            'agent'=>$this->useragent,
            'content' => 'inc-section-atelier.html.twig',
            'page'=>'atelier'
        ]);
    }


    #[Route('/initiation', name:"initiation")]
    public function initiation()
    {
        $vartwig=$this->navigator->dispatchinfo('initiation');
        return $this->render('contenus/pages/pg-initiation.html.twig', [
            'vartwig'=>$vartwig,
            'agent'=>$this->useragent,
            'content' => 'inc-section-initiation.html.twig',
            'page'=>'initiation'
        ]);
    }

}