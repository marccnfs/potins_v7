<?php


namespace App\Controller\MainPublic;

use App\Classe\UserSessionTraitOld;
use App\Lib\Links;
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
    use UserSessionTraitOld;

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
            $tab['post'],'showpost',Links::SHOWPOST );


        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'show',
            'replacejs'=>!empty($tab['posts']),
            'vartwig' => $vartwig,
            'contents'=>$tab['contents'],
            'mcdata'=>true,
            'potin'=>$tab['post'],
            'potins'=>$otherpotins,
            'ressources'=>$ressourcespotins,
            'entity'=>$tab['post']->getId(),
            'customer' => $this->customer,
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

        $vartwig = $this->menuNav->postinfoObj(
            $tab['post'],'showhistorypost',Links::SHOWPOST );

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'show',
            'replacejs'=>!empty($tab['posts']),
            'vartwig' => $vartwig,
            'mcdata'=>true,
            'events'=>$tabevent,
            'post'=>$tab['post'],
            'customer' => $this->customer,
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
                'showpotinreviewtyperesume',
                Links::SHOWPOST );

        }else{
            foreach ($gpreview->getReviews() as $review) {
                if (!$review->isType()){
                    $fichepdf=$review->getFiche()->getPdfreview();
                    break;
                }
            }
            $vartwig = $this->menuNav->postinfoObj(
                $post,
                'showpotinreviewtypetrame',
                Links::SHOWPOST );

        }

        return $this->render($this->useragentP.'ptn_pdfreview/home.html.twig', [
            'directory'=>'reviews',
            'replacejs'=>!empty($tab['posts']),
            'vartwig' => $vartwig,
            'mcdata'=>true,
            'post'=>$post,
            'entity'=>$post->getId(),
            'pdfreview'=>$fichepdf,
            'customer' => $this->customer,
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

        $vartwig = $this->menuNav->postinfoObj(
            $tab['post'],
            'showevent',
            Links::SHOWPOST );


        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'show',
            'replacejs'=>!empty($tab['posts']),
            'vartwig' => $vartwig,
            'content'=>$tab['content'],
            'mcdata'=>true,
            'post'=>$tab['post'],
            'posts'=>$tab['posts'],
            'events'=>$eventstab,
            'entity'=>$tab['post']->getId(),
            'customer' => $this->customer,
        ]);
    }

}
