<?php

namespace App\Controller\BoardOffice;


use App\Classe\UserSessionTrait;
use App\Lib\Links;
use App\Repository\OffresRepository;
use App\Repository\PostEventRepository;
use App\Repository\PostRepository;
use App\Service\Search\SearchRessources;
use App\Service\Search\SearchReviews;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_MEMBER')]
#[Route('/board-office')]

class BoardOfficeController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/tableau-de-bord', name:"office_member")]
    public function ospaceBlog(PostRepository $postationRepository): Response
    {
        $posts=$postationRepository->findPstKey($this->board->getCodesite());
        $vartwig=$this->menuNav->admin(
            $this->board,
            'ospaceblog',
            links::ADMIN,
            1
        );

        return $this->render($this->agentPrefix.'ptn_office/home.html.twig', [
            'directory'=>"potins",
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'member'=>$this->currentMember,
            'customer'=>$this->currentCustomer,
            'posts'=>array_reverse($posts),
        ]);
    }


    #[Route('/programmation-potins', name:"module_event")]
    public function ospaceEvent(PostEventRepository $eventRepository ): Response
    {

        $events=$eventRepository->findEventKey($this->board->getCodesite());

        $vartwig=$this->menuNav->admin(
            $this->board,
            'ospaceevent',
            links::ADMIN,
            2
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>"event",
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'member'=>$this->currentMember,
            'customer'=>$this->currentCustomer,
            'events'=>array_reverse($events),
            'locatecity'=>0
        ]);
    }

    #[Route('/offres-potins', name:"module_offre")]
    public function ospaceOffre(OffresRepository $offresRepository ): Response
    {
        $offres=$offresRepository->findOffreKey($this->board->getCodesite());

        $vartwig=$this->menuNav->admin(
            $this->board,
            'ospaceoffre',
            links::ADMIN,
            2
        );
        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>"offre",
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'member'=>$this->currentMember,
            'customer'=>$this->currentCustomer,
            'offres'=>array_reverse($offres),
            'locatecity'=>0
        ]);
    }

    #[Route('/list-ressources', name:"module_ressources")]
    public function ospaceRessource(SearchRessources $searchRessources): Response
    {
        $tabcarte=$searchRessources->findAllRessources();

        $vartwig=$this->menuNav->admin(
            $this->board,
            'ospaceressources',
            links::ADMIN,
            3
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>"ressources",
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'website'=>$this->board,
            'member'=>$this->currentMember,
            'customer'=>$this->currentCustomer,
            'articles'=>$tabcarte,
            'locatecity'=>0
        ]);
    }

    #[Route('/list-reviews', name:"module_reviews")]
    public function ospaceReview(SearchReviews $searchReviews): Response
    {
        $tabreviews=$searchReviews->findAllReviews();

        $vartwig=$this->menuNav->admin(
            $this->board,
            'ospacegpreviews',
            links::ADMIN,
            3
        );
        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>"review",
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'member'=>$this->currentMember,
            'customer'=>$this->currentCustomer,
            'reviews'=>$tabreviews,
        ]);
    }

}
