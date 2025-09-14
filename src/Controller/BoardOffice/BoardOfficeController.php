<?php


namespace App\Controller\BoardOffice;

use App\Classe\MemberSession;
use App\Email\RegistrationMailer;
use App\Lib\Links;
use App\Repository\BoardslistRepository;
use App\Repository\GpRessourcesRepository;
use App\Repository\OffresRepository;
use App\Repository\PostEventRepository;
use App\Repository\PostRepository;
use App\Repository\SuiviNotifRepository;
use App\Service\Search\Listpublications;
use App\Service\Search\SearchRessources;
use App\Service\Search\SearchReviews;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;


#[IsGranted('ROLE_MEMBER')]


class BoardOfficeController extends AbstractController
{
    use MemberSession;

    #[Route('/tableau-de-bord', name:"office_member")]
    public function ospaceBlog(PostRepository $postationRepository ): Response
    {
        $posts=$postationRepository->findPstKey($this->board->getCodesite());
        $vartwig=$this->menuNav->templatingadmin(
            'ospaceblog',
            $this->board->getNameboard(),
            $this->board,
            1
        );


        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>"potins",
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'posts'=>array_reverse($posts),
            'locatecity'=>0
        ]);
    }

    #[Route('/sendmail', name:"send_mail")]
    public function sendmail(RegistrationMailer $mailer ): Response
    {
        //$reponse=$mailer->sendtestMail();
        $reponse=$mailer->sendtestMailnoDkim();
        $vartwig=$this->menuNav->templatingadmin(
            'tesmail',
            $this->board->getNameboard(),
            $this->board,
            1
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>"testmail",
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'reponse'=>$reponse
        ]);
    }

    #[Route('/programmation-potins', name:"module_event")]
    public function ospaceEvent(PostEventRepository $eventRepository ): Response
    {
        $events=$eventRepository->findEventKey($this->board->getCodesite());
        $vartwig=$this->menuNav->templatingadmin(
            'ospaceevent',
            $this->board->getNameboard(),
            $this->board,
            2
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>"event",
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'events'=>array_reverse($events),
            'locatecity'=>0
        ]);
    }

    #[Route('/offres-potins', name:"module_offre")]
    public function ospaceOffre(OffresRepository $offresRepository ): Response
    {
        $offres=$offresRepository->findOffreKey($this->board->getCodesite());

        $vartwig=$this->menuNav->templatingadmin(
            'ospaceoffre',
            $this->board->getNameboard(),
            $this->board,
            2
        );
        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>"offre",
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'offres'=>array_reverse($offres),
            'locatecity'=>0
        ]);
    }

    #[Route('/list-ressources', name:"module_ressources")]
    public function ospaceRessource(SearchRessources $searchRessources, SuiviNotifRepository $notifRepository, $board=null, $i=null ): Response
    {
        $tabcarte=$searchRessources->findAllRessources();
        $vartwig=$this->menuNav->templatingadmin(
            'ospaceressources',
            $this->board->getNameboard(),
            $this->board,
            3
        );
        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>"ressources",
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'website'=>$this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'articles'=>$tabcarte,
            'locatecity'=>0
        ]);
    }

    #[Route('/list-reviews', name:"module_reviews")]
    public function ospaceReview(SearchReviews $searchReviews, SuiviNotifRepository $notifRepository, $board=null, $i=null ): Response
    {
        $tabreviews=$searchReviews->findAllReviews();
        $vartwig=$this->menuNav->templatingadmin(
            'ospacegpreviews',
            $this->board->getNameboard(),
            $this->board,
            3
        );
        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>"review",
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'website'=>$this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'reviews'=>$tabreviews,
        ]);
    }


// old ---------------------------------------------------------------------------------------------

    #[Route('/board/sucess/wp/show/{id?}', name:"show_wp")]
    public function officeBoard(Listpublications $listpublications, $board=null,$id=null): Response
    {
        $listmodule=$this->board->getListmodules();
        $notices=$listpublications->listPublicationsboard($this->board);

        $vartwig=$this->menuNav->templatingadmin(
            'office',
            $this->board->getNameBoard(),
            $this->board,
            1
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>"office",
            'replacejs'=>!empty($notices),
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'customer'=>$this->customer,
            'member'=>$this->member,
            'notices'=>$notices,
            'locatecity'=>0,
            'modules'=>$listmodule,
        ]);
    }


    #[Route('/board/Found/{board}', name:"module_found")]
    public function ospaceFound(GpRessourcesRepository $formulesRepository, $board=null, $i=null  ): Response
    {
        if($board!=$this->board->getId())$this->redirectToRoute('list_board');
        $formules=$formulesRepository->findByKey($this->board->getCodesite());
        $vartwig=$this->menuNav->templatingadmin(
            'ospacegpressources',
            $this->board->getNameboard(),
            $this->board,
            5
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>"gpressources",
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'member'=>$this->member,
            'formules'=>array_reverse($formules),
            'locatecity'=>0
        ]);
    }
    #[Route('/board/shop/{board}', name:"module_shop")]
    #[Route('/board/sucess/shop/show/{id?}', name:"show_shop")]
    public function ospaceShop(SuiviNotifRepository $notifRepository, OffresRepository $offresRepository, $city=null, $nameboard=null, $id=null ): Response
    {
        if($id){
            if(!$this->getUserspwsiteOfWebsite($id) || !$this->admin )$this->redirectToRoute('cargo_public');
        }else{
            if(!$this->member) throw new Exception('member inconnu');
            $this->activeBoard($nameboard);
        }
        $notifs=$notifRepository->findBy([
            "member"=>$this->member->getId()
        ]);
        $shops=$offresRepository->findOffreKey($this->board->getCodesite());

        $vartwig=$this->menuNav->templatingadmin(
            'ospaceshop',
            $this->board->getNameboard(),
            $this->board,
            4
        );
        return $this->render('aff_customer/home.html.twig', [
            'directory'=>"board",
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'website'=>$this->board,
            'member'=>$this->member,
            'shops'=>array_reverse($shops),
            'locatecity'=>0
        ]);
    }


    #[Route('/customer/board/mes_panneaux', name:'list_board')]
    public function listBoard(BoardslistRepository $spwsiteRepository): Response
    {

        $vartwig=$this->menuNav->newtemplateControlCustomer(
            Links::CUSTOMER_LIST,
            'listboard',
            "Mes panneaux",
            1
        );
        return $this->render('aff_member/home.html.twig', [
            'directory'=>"list",
            'replacejs'=>$replacejs??null,
            'vartwig'=>$vartwig,
            'member'=>$this->member,
            'board'=>$this->board,
            'website'=>$this->board,
            'locatecity'=>0
        ]);
    }



}