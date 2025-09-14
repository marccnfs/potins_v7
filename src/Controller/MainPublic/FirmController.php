<?php


namespace App\Controller\MainPublic;

use App\Classe\potinsession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class FirmController extends AbstractController
{
    use potinsession;


    #[Route('/a-propos', name:'apropos')]
    public function apropos()
    {
        $navigate=$this->navigator->navigshop('apropos');
        $insert['content']='inc-section-apropos.html.twig';
        $insert['titre']='votre guide de la living food';
        $insert['title']="votre guide de la living food";
        $insert['description']="mademoiselle gingembre - who I am?";

        return $this->render('contenus/pages/pg-whoiam.html.twig', [
            'links' =>$navigate['links'],
            'insert'=>$insert,
            'agent'=>$this->useragent,
            'page'=>'whoiam']);
    }


    #[Route('/mentions-legales', name:'legality')]
    public function legality()
    {
        $navigate=$this->navigator->navigshop('legality');
        $insert['content']='inc-section-legality.html.twig';
        $insert['titre']='votre guide de la living food';
        $insert['title']="votre guide de la living food";
        $insert['description']="mademoiselle gingembre - mentions légales";
        return $this->render('public/pg-mentionslegales.html.twig', [
            'links' =>$navigate['links'],
            'insert'=>$insert,
            'agent'=>$this->useragent,
            'page'=>'legality']);
    }


    #[Route('/confirmation-rdv', name:'confirm_rdv')]
    public function confirm()
    {
        $insert['titre']='confirmation rendez-vous';
        $insert['title']="Chenais énergies, à votre service";
        $insert['description']="mademoiselle gingembre - ";
        return $this->render('public/pg-confirmrdv.html.twig', [
            'insert'=>$insert,
            'page'=>'confirm',
            'agent'=>$this->useragent
        ]);
    }

}