<?php


namespace App\Service\Search;


use App\Entity\Module\GpReview;
use App\Repository\GpReviewRepository;
use App\Repository\PostEventRepository;
use App\Repository\PostRepository;
use App\Repository\BoardRepository;
use Doctrine\ORM\NonUniqueResultException;

class Searchmodule
{
    private PostRepository $postRepository;
    private PostEventRepository $postEventRepository;
    private BoardRepository $websiteRepository;
    private GpReviewRepository $gpReviewRepository;


    public function __construct(BoardRepository      $websiteRepository, PostEventRepository $postEventRepository,
                                PostRepository $postRepository, GpReviewRepository $gpReviewRepository)
    {

        $this->postRepository = $postRepository;
        $this->postEventRepository=$postEventRepository;
        $this->websiteRepository=$websiteRepository;
        $this->gpReviewRepository=$gpReviewRepository;
    }


    /**
     * @throws NonUniqueResultException
     */
    public function searchAllInfoWithReviewsAndRessourcesOfOnePotinId($id): bool|array
    {
        $contents=[];
        $post=$this->postRepository->findOnePostAndReviews($id);
        if($post->getHtmlcontent()){
            foreach ($post->getHtmlcontent() as $cont){
                if($cont->getFileblob()){
                    $contents[]=file_get_contents($cont->getphpPathblob());;
                }
            }
        }
        return ['post'=>$post,'contents'=>$contents];
    }


    /**
     * @param $id
     * @return bool|array
     * @throws NonUniqueResultException
     */
    public function searchOnePostAndListAndMsg($id): bool|array
    {
        $posts=[];
        $post=$this->postRepository->findOnePostAndMsg($id);

        if($post){
            $board=$this->websiteRepository->findWbByKey($post->getKeymodule());
            if($post->getHtmlcontent()->getFileblob()){
                $content=file_get_contents($post->getHtmlcontent()->getphpPathblob());
            }else{
                $content="";
            }
            $posts=$this->postRepository->findPostsByKeyWithOutId($post->getKeymodule(),$id);
            return ['board'=>$board,'posts'=>$posts, 'post'=>$post,'content'=>$content, 'key'=>$post->getKeymodule(), "msgp"=>$post->getTbmessages()];
        }else{
            return false;
        }
    }

    public function searchAllPotinsOther(): bool|array
    {
        return $this->postRepository->findAllPost();
    }

    public function searchAllOtherPotinsWithOutThisOne($id): bool|array
    {
        return $this->postRepository->findAllPotinsActivWithOutPotinsId($id);
    }

    public function searchOnePotinAndReviewsWithOtherPotins($id): bool|array
    {
        $contents=[];
        // if(!$post=$this->postRepository->findOnePostAndReviews($id)) return false;
        $events=$this->postEventRepository->findEventByIdPotin($id);
        $post=$events[0]->getPotin();
        //$board=$this->websiteRepository->findWbByKey($post->getKeymodule()); todo reactiver si besoin
        if($post->getHtmlcontent()){
            foreach ($post->getHtmlcontent() as $cont){
                if($cont->getFileblob()){
                    $contents[]=file_get_contents($cont->getphpPathblob());;
                }

            }
        }
        //$posts=$this->postRepository->findAllPotinsActivWithOutPotinsId($post->getId()); // todo reactiver si besoin
        return ['board'=>[],'posts'=>[],'events'=>$events, 'post'=>$post,'contents'=>$contents, 'key'=>$post->getKeymodule()/*, "msgp"=>$post->getTbmessages()*/];    }


    /**
     * @throws NonUniqueResultException
     */
    public function searchReviewByIdPotin($id): GpReview
    {
        return $this->gpReviewRepository->findGpreviewsAllByPost($id);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function searchOnePotinAndReview($id): bool|array
    {
        //$contents=[];
        if(!$post=$this->postRepository->findOnePostAndReviews($id)) return false;

        //$board=$this->websiteRepository->findWbByKey($post->getKeymodule());
        /*
        if($post->getHtmlcontent()){
            foreach ($post->getHtmlcontent() as $cont){
                if($cont->getFileblob()){
                    $contents[]=file_get_contents($cont->getphpPathblob());;
                }
            }
        }
        */
        //$posts=$this->postRepository->findAllPotinsActivWithOutPotinsId($post->getId()); // todo reactiver si besoin
        return ['board'=>[],'posts'=>[],'post'=>$post,'contents'=>[] /*'key'=>$post->getKeymodule(), "msgp"=>$post->getTbmessages()*/];
    }

    public function searchEventWithPostAndBoard($id): bool|array
    {
        //$event=$this->postEventRepository->findEventById($id);
        $events=$this->postEventRepository->findAllEventsByIdPotin($id);
        if($events){
            $potin=$this->postRepository->find($events[0]['potin']['id']);
            $board=$this->websiteRepository->findWbByKey($events[0]['keymodule']);
            if($potin->getHtmlcontent()->getFileblob()){
                $content=file_get_contents($potin->getHtmlcontent()->getphpPathblob());
           /* if($potin['htmlcontent']['fileblob']){
               $dir= __DIR__ . '/../../../public/5764xs4m/blobtxt8_4/'.$potin['htmlcontent']['fileblob'];
                //$content=file_get_contents($potin['htmlcontent']['phpPathblob']);
                $content=file_get_contents($dir);
           */
            }else{
                $content="";
            }
            $posts=$this->postRepository->findAllPotinsActivWithOutPotinsId($potin->getId());
            return ['events'=>$events,'board'=>$board,'posts'=>$posts, 'post'=>$potin,'content'=>$content, 'key'=>$events[0]['keymodule']];
        }else{
            return false;
        }
    }

    public function findLastBeforeWeek(): bool|array
    {

        $events=$this->postEventRepository->findLastBeforeWeek();
        $tabevents=[];
        if($events){
            foreach ($events as $key=> $event){
                $tabevents[$event->getPotin()->getId()][]=$event;
            }
            //$potin=$this->postRepository->find($events[0]['potin']['id']);
           // $board=$this->websiteRepository->findWbByKey($events[0]['keymodule']);

            return $tabevents;
        }else{
            return false;
        }
    }

    /**
     * @param $id
     * @return bool|array
     * @throws NonUniqueResultException
     */
    public function searchOnePostAndMsgP($id): bool|array
    {

        $post=$this->postRepository->findOnePostAndMsg($id);

        if($post){
            $board=$this->websiteRepository->findWbByKey($post->getKeymodule());
            if($post->getHtmlcontent()->getFileblob()){
                $content=file_get_contents($post->getHtmlcontent()->getphpPathblob());
            }else{
                $content="";
            }

            return ['board'=>$board, 'post'=>$post,'content'=>$content, 'key'=>$post->getKeymodule(), "msgp"=>$post->getTbmessages()];
        }else{
            return false;
        }
    }

}
