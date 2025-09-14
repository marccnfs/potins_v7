<?php


namespace App\Service\Search;


use App\Entity\Module\GpReview;
use App\Entity\Module\ModuleList;
use App\Repository\GpReviewRepository;
use App\Repository\RessourcesRepository;
use App\Repository\GpRessourcesRepository;
use App\Repository\OffresRepository;
use App\Repository\PostEventRepository;
use App\Repository\PostRepository;
use App\Repository\BoardRepository;
use Doctrine\ORM\NonUniqueResultException;

class Searchmodule_aff
{

    private OffresRepository $offreRepository;
    private PostRepository $postRepository;
    private PostEventRepository $postEventRepository;
    private BoardRepository $websiteRepository;
    private RessourcesRepository $articlesFormuleRepository;
    private GpRessourcesRepository $formuleRepository;
    private GpReviewRepository $gpreviwrepository;


    public function __construct(BoardRepository      $websiteRepository, PostEventRepository $postEventRepository,
                                OffresRepository     $offreRepository, PostRepository $postRepository,
                                RessourcesRepository $articlesFormuleRepository, GpRessourcesRepository $formulesRepository, GpReviewRepository $gpReviewRepository)
    {
        $this->offreRepository = $offreRepository;
        $this->postRepository = $postRepository;
        $this->postEventRepository=$postEventRepository;
        $this->websiteRepository=$websiteRepository;
        $this->articlesFormuleRepository=$articlesFormuleRepository;
        $this->formuleRepository=$formulesRepository;
        $this->gpreviwrepository=$gpReviewRepository;
    }


    public function findModule($wb, $classmodule): bool| ModuleList
    {
        $listModules=$wb->getListmodules();
        if (!empty($listModules)) {
            foreach ($listModules as $modList) {
                if ($modList->getClassmodule() === $classmodule) {
                    return $modList;
                }
            }
        }
        return false;
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
        //$posts=$this->postRepository->findAllPotinsActivWithOutPotinsId($post->getId()); // todo reactiver si besoin
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


    /**
     * @throws NonUniqueResultException
     */
    public function searchOnePotinAndMsgWithOtherPotins($id): bool|array
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
            $posts=$this->postRepository->findAllPotinsActivWithOutPotinsId($post->getId());
            return ['board'=>$board,'posts'=>$posts, 'post'=>$post,'content'=>$content, 'key'=>$post->getKeymodule(), "msgp"=>$post->getTbmessages()];
        }else{
            return false;
        }
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


    public function searchReviewByIdPotin($id): GpReview
    {
        return $this->gpreviwrepository->findGpreviewsAllByPost($id);
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



    public function searchOnePotinAndRessources($id): bool|array
    {
        if(!$post=$this->postRepository->findOnePostAndReviews($id)) return false; // a finir renvoi que les ressources
        return ['board'=>[],'posts'=>[],'post'=>$post,'contents'=>[]];
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

    /**
     * @param $id
     * @return array|bool
     * @throws NonUniqueResultException
     */
    public function searchOneOffreandList($id): bool|array
    {
        $offre=$this->offreRepository->findOneOffre($id);
        if($offre){
            $board=$this->websiteRepository->findWbByKey($offre->getKeymodule());
            if( $offre->getProduct()->getHtmlcontent()->getFileblob()){
                $content=file_get_contents($offre->getProduct()->getHtmlcontent()->getWebPathblob());
            }else{
                $content="";
            }
            $offres=$this->offreRepository->findOffresByKeyWithOutId($offre->getKeymodule(),$id);
            return ['board'=>$board,'offres'=>$offres, 'offre'=>$offre,'content'=>$content, 'key'=>$offre->getKeymodule()];
        }else{
            return false;
        }
    }

    /**
     * @param $id
     * @return array|bool
     * @throws NonUniqueResultException
     */
    public function searchOneEventandList($id): bool|array
    {
        $event=$this->postEventRepository->findEventById($id);
        if($event){
            $board=$this->websiteRepository->findWbByKey($event->getKeymodule());
            $events=$this->postEventRepository->findEventsByKeyWithOutId($event->getKeymodule(), $id);
            return ['events'=>$events, 'event'=>$event, 'board'=>$board];
        }else{
            return false;
        }
    }


    /**
     * @param $id
     * @return array|bool
     * @throws NonUniqueResultException
     */
    public function searchOneMenuandList($id): bool|array
    {
        $menu=$this->formuleRepository->findFormuleById($id);
        if($menu){
            $board=$this->websiteRepository->findWbByKey($menu->getKeymodule());
            $menus=$this->formuleRepository->findFormulessByKeyWithOutId($menu->getKeymodule(), $id);
            return ['menus'=>$menus, 'menu'=>$menu, 'board'=>$board];
        }else{
            return false;
        }
    }

    public function findCarte($key): array
    {
        $tabcarte=['entree'=>[],'plat'=>[],'dessert'=>[],'boisson'=>[]];
        if ($key) {
            $cartes=$this->articlesFormuleRepository->findAllByKey($key);
            if(!empty($cartes)){
                foreach ($cartes as $carte){
                    if($carte->getCategorie()->getId()==1)$tabcarte['entree'][]=$carte;
                    if($carte->getCategorie()->getId()==2)$tabcarte['plat'][]=$carte;
                    if($carte->getCategorie()->getId()==3)$tabcarte['dessert'][]=$carte;
                    if($carte->getCategorie()->getId()==4)$tabcarte['boisson'][]=$carte;
                }
                return $tabcarte??[];
            }
        }
        return $tabcarte;
    }





}