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

#[Route('/mediatheque')]

class MediathequesController extends AbstractController
{
    use publicSession;

    #[Route('/{slug}', name:"media_page")]
    public function mediaMontagne(BoardRepository $boardRepository,SectorsRepository $reposector,ListEvent $listEvent,Calopen $calopen, $slug): Response
    {
        $media=$boardRepository->findWbBySlug($slug);
        $sector=$reposector->findWithAdressByCodesite($media->getCodesite());
        $notices=$listEvent->listallEvenstResa($media->getId());

       $vartwig=$this->menuNav->templatepotins('mediatheque', Links::MEDIATHEQUE);

            return $this->render($this->useragentP.'ptn_public/home.html.twig', [
                'directory'=>'board',
                'replacejs'=>false,
                'vartwig'=>$vartwig,
                'board'=>$media,
                'media'=>$media,
                'sector'=>$sector,
                'notices'=>$notices,
                'openday'=>$media->getTabopendays()? $calopen->cal($media->getTabopendays()):"",
            ]);
    }

}
