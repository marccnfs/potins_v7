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
    #[Route('potin/{slug}/{id}', name:"show_potin", requirements: ['id' => '\\d+'])]
    public function showPotin(
        SearchRessources $searchRessources,
        Searchmodule $searchmodule,
        int $id,
        string $slug
    ): Response {
        $potinData = $searchmodule->searchAllInfoWithReviewsAndRessourcesOfOnePotinId($id);
        $eventData = $searchmodule->searchEventWithPostAndBoard($id);

        if ((!is_array($potinData) || empty($potinData['post'])) && (!is_array($eventData) || empty($eventData['post']))) {
            return $this->redirectToRoute('board_all');
        }

        $post = $potinData['post'] ?? $eventData['post'];
        if (!$post) {
            return $this->redirectToRoute('board_all');
        }

        if (method_exists($post, 'getSlug')) {
            $canonicalSlug = (string) $post->getSlug();
            if ($canonicalSlug !== '' && $canonicalSlug !== $slug) {
                return $this->redirectToRoute('show_potin', [
                    'slug' => $canonicalSlug,
                    'id' => $post->getId(),
                ], 301);
            }
        }
        $contents = [];
        if (is_array($potinData) && !empty($potinData['contents'])) {
            $contents = $potinData['contents'];
        } elseif (is_array($eventData) && !empty($eventData['content'])) {
            $eventContent = $eventData['content'];
            $contents = is_array($eventContent) ? $eventContent : [$eventContent];
        }

        $otherPotins = [];
        if (is_array($potinData) && !empty($potinData['post'])) {
            $otherPotins = $searchmodule->searchAllOtherPotinsWithOutThisOne($post->getId()) ?: [];
        } elseif (is_array($eventData) && isset($eventData['posts'])) {
            $otherPotins = $eventData['posts'] ?: [];
        }

        $ressources = [];
        if (method_exists($post, 'getGpressources') && $post->getGpressources()) {
            $ressources = $searchRessources->findRessourcesOfPotins($post->getGpressources()->getId());
        }

        $events = is_array($eventData) ? ($eventData['events'] ?? []) : [];
        $eventSummary = is_array($eventData) ? ($eventData['eventSummary'] ?? null) : null;

        $vartwig = $this->menuNav->postinfoObj(
            $post,
            'showpost',
            Links::SHOWPOST
        );

        return $this->render($this->useragentP . 'ptn_public/home.html.twig', [
            'directory' => 'show',
            'replacejs' => !empty($otherPotins),
            'vartwig' => $vartwig,
            'contents' => $contents,
            'mcdata' => true,
            'post' => $post,
            'posts' => $otherPotins,
            'events' => $events,
            'eventSummary' => $eventSummary,
            'ressources' => $ressources,
            'entity' => $post->getId(),
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

        if(!is_array($tab) || empty($tab['post']))return $this->redirectToRoute('board_all');

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
}
