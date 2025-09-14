<?php


namespace App\Controller\MainPublic;

use App\Classe\PublicSession;
use App\Module\Reviewscator;
use App\Repository\DocstoreRepository;
use App\Repository\PostEventRepository;
use App\Service\Modules\Resator;
use App\Service\Search\ListEvent;
use App\Service\Search\Searchmodule;
use App\Service\Search\SearchRessources;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShowPublicationsController extends AbstractController
{
    use PublicSession;

    /**
     * @throws NonUniqueResultException
     */
    #[Route('potin/{slug}/{id}', name:"show_potin")]
    public function showPotin(SearchRessources $searchRessources,Searchmodule $searchmodule,ListEvent $listEvent,$id,$slug): Response
    {
        $tab=$searchmodule->searchAllInfoWithReviewsAndRessourcesOfOnePotinId($id);
        if(!$tab['post'])return $this->redirectToRoute('board_all');

        $otherpotins=$searchmodule->searchAllOtherPotinsWithOutThisOne($id);


        if($tab['post']->getGpressources()){
            $ressourcespotins=$searchRessources->findRessourcesOfPotins($tab['post']->getGpressources()->getId());
        }else{
            $ressourcespotins=[];
        }


        $vartwig = $this->menuNav->postinfoObj(
            $tab['post'],
            $this->board,
            'showpost',
            0,
            $tab['post']->getTitre(),
            'all');


        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'show',
            'replacejs'=>!empty($tab['posts']),
            'vartwig' => $vartwig,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'contents'=>$tab['contents'],
            'mcdata'=>true,
            'potin'=>$tab['post'],
            'potins'=>$otherpotins,
            'ressources'=>$ressourcespotins,
            'entity'=>$tab['post']->getId(),
        ]);
    }


    #[Route('/download-doc-participant/{id}/{idpotin}', name:"download_doc_participant")]
    public function downLoadDoc($id, $idpotin, DocstoreRepository $docstoreRepository)
    {
        $fichier=$docstoreRepository->find($id);
        if ($fichier == null){
            return $this->redirectToRoute('potin-history', ['id'=>$idpotin]); }
        else{
            return $this->file($this->getParameter('upload_directory').'/'.$fichier->getName(), $fichier->getNomOriginal());
        }
    }

    #[Route('potin-history/{id}', name:"showpotins_history")]
    public function showPotinHistory(ListEvent $listEvent,Searchmodule $searchmodule,PostEventRepository $postEventRepository,$id): Response
    {
        $tabevent=[];
        $tab=$searchmodule->searchOnePotinAndReview($id);

        if(!$tab['post'])return $this->redirectToRoute('board_all');

        $events=$postEventRepository->findEventByOnePotin($tab['post']->getId());

        foreach ($events as $event){
            $tabevent[$event->getId()]['event']=$event;
            $tabevent[$event->getId()]['orders']=$listEvent->listParticipantPotin($event->getId());
        }

        //$this->board=$tab['board'];

        if($this->member){
            //$twigfile,$page, $title, Board $website
            $vartwig=$this->menuNav->templateMember(
                'showhistorypost',
                2,
                $this->board);

        }else{
            $vartwig = $this->menuNav->postinfoObj(
                $tab['post'],
                $this->board,
                'showhistorypost',
                0,
                $tab['post']->getTitre(),
                'all');
        }


        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'show',
            'replacejs'=>!empty($tab['posts']),
            'vartwig' => $vartwig,
            'member'=>$this->member,
            'customer'=>$this->customer,
            //'contents'=>$tab['contents'],
            'mcdata'=>true,
            'events'=>$tabevent,
            'post'=>$tab['post'],
            //'ressources'=>[],
            //'potins'=>$tab['posts'],
            //'entity'=>$tab['post']->getId(),
            //'board'=>$this->board,
        ]);
    }


    /**
     * @throws NonUniqueResultException
     */
    #[Route('potin-review-pdf/{type}/{slug}/{id}', name:"show_potin_review_pdf")]
    public function showPostReviewPdf(Searchmodule $searchmodule,$id,$slug,$type): Response
    {
        $gpreview=$searchmodule->searchReviewByIdPotin($id);
        $post=$gpreview->getPotin();
        if(!$post)return $this->redirectToRoute('board_all');
        $fichepdf='';
        if($type=="1"){
            foreach ($gpreview->getReviews() as $review) {
                if ($review->isType()){
                    $fichepdf=$review->getFiche()->getPdfreview();
                    break;
                }
            }
            $vartwig = $this->menuNav->postinfoObj(
                $post,
                $this->board,
                'showpotinreviewtyperesume',
                0,
                $post->getTitre(),
                'all');

        }else{
            foreach ($gpreview->getReviews() as $review) {
                if (!$review->isType()){
                    $fichepdf=$review->getFiche()->getPdfreview();
                    break;
                }
            }
            $vartwig = $this->menuNav->postinfoObj(
                $post,
                $this->board,
                'showpotinreviewtypetrame',
                0,
                $post->getTitre(),
                'all');
        }

        return $this->render($this->useragentP.'ptn_pdfreview/home.html.twig', [
            'directory'=>'reviews',
            'replacejs'=>!empty($tab['posts']),
            'vartwig' => $vartwig,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'mcdata'=>true,
            'post'=>$post,
            'entity'=>$post->getId(),
            'pdfreview'=>$fichepdf
        ]);
    }

    /**
     * @throws NonUniqueResultException
     */
    #[Route('test-pdf/{type}/{id}', name:"test_pdf")]
    public function testPdf(Searchmodule $searchmodule,$id,$type): Response
    {
        $tab=$searchmodule->searchOnePotinAndReviewsWithOtherPotins($id);
        if(!$tab['post'])return $this->redirectToRoute('board_all');
        $this->board=$tab['board'];
        $fichepdf='';
        if($type=="1"){
            foreach ($tab['post']->getGpreview()->getReviews() as $review) {
                if ($review->isType()){
                    $fichepdf=$review->getFiche()->getPdfreview();
                    break;
                }
            }
            $vartwig = $this->menuNav->postinfoObj(
                $tab['post'],
                $this->board,
                'pdftest',
                0,
                $tab['post']->getTitre(),
                'all');

        }else{
            foreach ($tab['post']->getGpreview()->getReviews() as $review) {
                if (!$review->isType()){
                    $fichepdf=$review->getFiche()->getPdfreview();
                    break;
                }
            }
            $vartwig = $this->menuNav->postinfoObj(
                $tab['post'],
                $this->board,
                'pdftest',
                0,
                $tab['post']->getTitre(),
                'all');
        }

        return $this->render($this->useragentP.'test/reviews/pdftest.html.twig', [
            'vartwig' => $vartwig,
            'content'=>$tab['content'],
            'review' => $review,
            'potin'=>$tab['post'],
            'pdfreview'=>$fichepdf
        ]);
    }

    /**
     * @throws NonUniqueResultException
     */
    #[Route('show-pdf-test/{id}', name:"show_test_pdf")]
    public function ShowTestPdf(Searchmodule $searchmodule, Reviewscator $reviewscator,$id): Response
    {
        $tab=$searchmodule->searchOnePotinAndReviewsWithOtherPotins($id);

        if(!$tab['post'])return $this->redirectToRoute('board_all');
        $this->board=$tab['board'];
        $rw='';

        foreach ($tab['post']->getGpreview()->getReviews() as $review) {
            if (!$review->isType()){ //fiche trame
                $rw=$review;
                break;
            }
        }


        $rw=$reviewscator->testManageReviewAjaxForPdf($rw, $tab['post']);

        $vartwig = $this->menuNav->postinfoObj(
            $tab['post'],
            $this->board,
            'showpotinreviewtypetrame',
            0,
            $tab['post']->getTitre(),
            'all');


            return $this->render($this->useragentP.'ptn_pdfreview/home.html.twig', [
            'directory'=>'reviews',
            'replacejs'=>!empty($tab['posts']),
            'vartwig' => $vartwig,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'content'=>$tab['content'],
            'mcdata'=>true,
            'post'=>$tab['post'],
            'ressources'=>[],
            'posts'=>$tab['posts'],
            'entity'=>$tab['post']->getId(),
            'board'=>$this->board,
            'pdfreview'=>$rw->getFiche()->getPdfreview()

            ]);
    }


    #[Route('eventshow/{id}', name:"show_event_id")]
    public function showEventId(Searchmodule $searchmodule,Resator $resator, $id ): Response
    {
        $tab=$searchmodule->searchEventWithPostAndBoard($id);
        if(!$tab)return $this->redirectToRoute('board_all');
        $eventstab=$tab['events'];
        foreach ($eventstab as &$ev){
            $finddate=$resator->listDatesEvent($ev);
            $ev['dates']=$finddate;
        }

        $this->board=$tab['board'];

        if($this->member){
            //$twigfile,$page, $title, Board $website
            $vartwig=$this->menuNav->templateMember(
                'showeventid',
                2,
                $this->board);

        }else{
            $vartwig = $this->menuNav->postinfoObj(
                $tab['post'],
                $this->board,
                'showevent',
                0,
                $tab['events'][0]['titre'],
                'all');
        }

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'show',
            'replacejs'=>!empty($tab['posts']),
            'vartwig' => $vartwig,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'content'=>$tab['content'],
            'mcdata'=>true,
            'post'=>$tab['post'],
            'posts'=>$tab['posts'],
            'events'=>$eventstab,
            'entity'=>$tab['post']->getId(),
            'board'=>$this->board,
        ]);
    }

}