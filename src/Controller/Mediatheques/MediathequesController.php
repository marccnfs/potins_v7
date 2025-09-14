<?php


namespace App\Controller\Mediatheques;


use App\Classe\PublicSession;
use App\Lib\Calopen;
use App\Lib\Links;
use App\Repository\BoardRepository;
use App\Repository\SectorsRepository;
use App\Service\Search\ListEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('mediatheque')]

class MediathequesController extends AbstractController
{
    use publicSession;

    #[Route('/{slug}', name:"media_page")]
    public function mediaMontagne(BoardRepository $boardRepository,SectorsRepository $reposector,ListEvent $listEvent,Calopen $calopen, $slug): Response
    {
        $media=$boardRepository->findWbBySlug($slug);
        $sector=$reposector->findWithAdressByCodesite($media->getCodesite());
        $notices=$listEvent->listallEvenstResa($media->getId());
           $vartwig=$this->menuNav->templatepotins(
                Links::ACCUEIL,
                'mediatheque',
                0,
                "nocity");

            return $this->render($this->useragentP.'ptn_public/home.html.twig', [
                'directory'=>'board',
                'replacejs'=>false,
                'customer'=>$this->customer,
                'vartwig'=>$vartwig,
                'board'=>$media,
                'media'=>$media,
                'sector'=>$sector,
                'notices'=>$notices,
                'openday'=>$this->board->getTabopendays()? $calopen->cal($this->board->getTabopendays()):"",
            ]);
    }

    #[Route('mediatheque-la-saint-jean-de-boiseau', name:"media_saintjean")]
    public function mediaSaintJean(): Response
    {
        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            'indexpublic',
            0,
            "nocity");

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'main',
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('mediatheque-le-pellerin', name:"media_pellerin")]
    public function mediaPellerin(): Response
    {
        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            'indexpublic',
            0,
            "nocity");

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'main',
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig
        ]);
    }


}