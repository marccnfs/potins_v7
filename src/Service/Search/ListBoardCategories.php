<?php


namespace App\Service\Search;


use App\Entity\Member\Boardslist;
use App\Entity\Marketplace\Offres;
use App\Entity\Module\PostEvent;
use App\Entity\Posts\Post;
use App\Entity\Boards\Board;
use App\Repository\OffresRepository;
use App\Repository\PostEventRepository;
use App\Repository\PostRepository;
use App\Repository\BoardRepository;


class ListBoardCategories
{


    private BoardRepository $boardRepository;


    public function __construct(BoardRepository $boardRepository, PostRepository $postRepository)
    {
        $this->boardRepository=$boardRepository;
    }

    public function listBoardAll(): array
    {
        $tabhit=[];
        $tabloc=[];
        $listboard=[];
        $sortBoard=[];
        $tabidwebsite=[];
        $wbs=$this->boardRepository->findWebsiteAll();
        foreach ($wbs as $wb) {
            $tabidwebsite[$wb['id']]=$wb;
            $listboard[$wb['codesite']] = $wb;
            if( $wb['statut']) $tabloc[]=$wb;// pour identification du board de la marie locale

            if($wb['hits']){ // normalement ils ont tous un hit
                foreach ($wb['hits']['catag'] as $cat) {
                    if ($cat['name'] != "") {
                        $tabhit[] = $cat['name'];
                    }
                }
                $sortBoard[$wb['id']]=($wb['hits']['liked']+($wb['hits']['publi']));
                arsort($sortBoard, $flags=SORT_NUMERIC);
            }

        }

        $countscore=array_count_values($tabhit);
        arsort($countscore, $flags=SORT_NUMERIC);
        //todo mettre une limite de tag

        foreach ($countscore as $key => $sc){
            foreach ($wbs as  $wb) {
                if ($wb['hits']) {
                    foreach ($wb['hits']['catag'] as $cat) {
                        if ($cat['name'] === $key) {
                            $sortBoard[$wb['id']] = $sortBoard[$wb['id']] + $sc;
                        }
                    }
                }
            }
        }
        arsort($sortBoard, $flags=SORT_NUMERIC);
        return['listboard'=>$listboard, 'wbs'=>$tabidwebsite, 'score'=>$countscore, 'orderwbs'=>$sortBoard];

    }

    /**
     * @param $wb Board
     * @return array
     */
    public function listPublicationsboard(Board $wb): array
    {
        $taball=[];
        $posts=$this->postRepository->findAllByKey($wb->getCodesite());
        foreach ($posts as $post){
            $taball[$post->getCreateAt()->getTimestamp()] = $post;
        }
        $offres=$this->offreRepository->findAllByKey($wb->getCodesite());
        foreach ($offres as $offre){
            $taball[$offre->getCreateAt()->getTimestamp()] = $offre;
        }
        krsort($taball);

        return ['taball'=>$taball];
    }



    // todo a voir le reste en dessous.......






    /**
     * @param $city
     * @param $listboard
     * @return array
     */
    public function listPublicationsThanCity($city,$listboard): array
    {
        $taball=[];
        $posts=$this->postRepository->findlastBycity($city);

        /** @var Post $post */
        foreach ($posts as $post){
            if($post && array_key_exists($post->getKeymodule(),$listboard)){
                $taball[$post->getCreateAt()->getTimestamp()]=['board'=>$listboard[$post->getKeymodule()], "post"=>$post];
            }
        }
        $offres=$this->offreRepository->findlastBycity($city);
        /** @var Offres $offre */
        foreach ($offres as $offre){
            if($offre && array_key_exists($offre->getKeymodule(),$listboard)) {
                $taball[$offre->getCreateAt()->getTimestamp()] = ['board' => $listboard[$offre->getKeymodule()], "offre" => $offre];
            }
        }
        krsort($taball);
        return $taball;
    }


    /**
     * @param $city
     * @param $listboard
     * @return array
     */
    public function findAllEventsOfCityBeforeOneWeek($city,$listboard): array
    {
        $taball=[];
        $events=$this->postEventRepository->findLastByCityBeforeWeek($city);

        /** @var PostEvent $event */
        foreach ($events as $event){
            if(array_key_exists($event->getKeymodule(),$listboard)){
                $taball[$event->getCreateAt()->getTimestamp()]=['board'=>$listboard[$event->getKeymodule()], "event"=>$event];
            }
        }
        krsort($taball);
        return $taball;
    }



    //todo revoir le nombre de requete pour tourver le boad correspondant a la publication (nb:27/10/21)
    /**
     * @param $locate
     * @param $listboard
     * @return array
     */
    public function listPublicationsThanCityArround($locate, $listboard): array
    {
        $taball=[];
        $posts=$this->postRepository->findAroundlastBycity($locate);
        /** @var Post $post */
        foreach ($posts as $post){
            $taball[$post->getCreateAt()->getTimestamp()]=['board'=>$this->websiteRepository->findWbByKey($post->getKeymodule()), "post"=>$post];
        }
        $offres=$this->offreRepository->findAroundlastBycity($locate);
        /** @var Offres $offre */
        foreach ($offres as $offre){
            $taball[$offre->getCreateAt()->getTimestamp()]=['board'=>$this->websiteRepository->findWbByKey($offre->getKeymodule()), "offre"=>$offre];
        }
        krsort($taball);
        return $taball;
    }


    /**
     * @param $city
     * @return array
     */
    public function listBoardByHitsThanCity($city): array
    {
        $tabhit=[];
        $tabloc=[];
        $listboard=[];
        $sortBoard=[];
        $tabidwebsite=[];
        $wbs=$this->websiteRepository->findWebsiteOfLocate($city);
        foreach ($wbs as $wb) {
            $tabidwebsite[$wb['id']]=$wb;
            $listboard[$wb['codesite']] = $wb;
            if( $wb['statut']) $tabloc[]=$wb;// pour identification du board de la marie locale

            if($wb['hits']){ // normalement ils ont tous un hit
                foreach ($wb['hits']['catag'] as $cat) {
                    if ($cat['name'] != "") {
                        $tabhit[] = $cat['name'];
                    }
                }
                $sortBoard[$wb['id']]=($wb['hits']['liked']+($wb['hits']['publi']));
                arsort($sortBoard, $flags=SORT_NUMERIC);
            }
        }

        $countscore=array_count_values($tabhit);
        arsort($countscore, $flags=SORT_NUMERIC);
        //todo mettre une limite de tag


        foreach ($countscore as $key => $sc){
            foreach ($wbs as  $wb) {
                if ($wb['hits']) {
                    foreach ($wb['hits']['catag'] as $cat) {
                        if ($cat['name'] === $key) {
                            $sortBoard[$wb['id']] = $sortBoard[$wb['id']] + $sc;
                        }
                    }
                }
            }
        }
         arsort($sortBoard, $flags=SORT_NUMERIC);
        return['listboard'=>$listboard, 'wbs'=>$tabidwebsite, 'score'=>$countscore, 'orderwbs'=>$sortBoard];

     }


     public function Listformule($listmodule){
         if(!empty($listmodule)){
             foreach ($listmodule as $mod) {
                 if ($mod->getClassmodule() == "formule") {
                    return $this->formulesRepository->findformuleKey($mod->getKeymodule());
                 }
             }
         }
         return [];
     }

     public function Listevent($listmodule){
         if(!empty($listmodule)){
             foreach ($listmodule as $mod) {
                 if ($mod->getClassmodule() == "module_event") {
                     return $this->postEventRepository->findEventKey($mod->getKeymodule());
                 }
             }
         }
         return [];
     }

     /**
      * @param $key
      * @return array
      */
    public function listOffres($key): array
    {
        return $this->offreRepository->ListOffresByKey($key);
    }

    /**
     * @param $wb Board
     * @return array
     */
    public function listModuleOfWebsite(Board $wb): array
    {
        $listModule=$wb->getListmodules();
        if (!empty($listmodule)) {
            foreach ($listModule as $mod) {
                switch ($mod->getClassmodule()) {
                    case"formule":
                        $tabmodule['food'] = true;
                        break;
                    case"now":
                        $tabmodule['now'] = true;
                        break;
                    case"buble":
                        $tabmodule['buble'] = true;
                        break;
                    case"shop":
                        $tabmodule['shop'] = true;
                        break;

                }
            }
        }
        if($module->getContactation()) $tabmodule['conv'] = true;
       // if($module->getReservation()) $tabmodule['resa'] = true;
       // if($module->getEnventation()) $tabmodule['event'] = true;
        return $tabmodule;
    }



    public function listPublications($id){
        $tabpost=[];
        $taboffre=[];
        $posts=$this->postRepository->ListpostofOneWb($id);
        /** @var Post $post */
        foreach ($posts as $post){
            if(!$post->getDeleted()){
                $tabpost[$post->getCreateAt()->getTimestamp()]=$post;
            }
        }
        $offres=$this->offreRepository->ListOffresOfOneWb($id);
        /** @var Offres $offre */
        foreach ($offres as $offre){
            $taboffre[$offre['createAt']->getTimestamp()]=$offre;
        }
        krsort($taboffre);
        krsort($tabpost);

        if(count($taboffre)>0 && count($tabpost)>0){

            if(current($tabpost) > current($taboffre)){
                $tablat['entity']=current($tabpost);
                $tablat['type']='post';
            }else{
                $tablat['entity']=current($taboffre);
                $tablat['type']='offre';
            }
        }elseif(count($taboffre)> 0){
            $tablat['entity']=current($taboffre);
            $tablat['type']='offre';
        }elseif(count($tabpost)> 0){
            $tablat['entity']=current($tabpost);
            $tablat['type']='post';
        }else{
            $tablat=[];
        }
        return ['posts'=>$tabpost, 'offres'=>$taboffre, 'last'=>$tablat];
    }

    /**
     * @param $wb Board
     * @param $listmodule
     * @return array
     */
    public function listPublicationsAndModules(Board $wb, $listmodule): array
    {

        $tabevent=[];
        $tabpost=[];
        $tabformule=[];
        $tabshop=[];

        foreach ($listmodule as $module){

            switch ($module->getClassmodule()){
                case "module_event":
                    $event = $this->postEventRepository->findlastByKey($wb->getCodesite());
                    if($event){
                        $tabevent[$event[0]->getCreateAt()->getTimestamp()] = $event[0];
                    }
                    break;

                case "module_blog":
                    $post=$this->postRepository->findLastByKey($wb->getCodesite());
                    if($post){
                        $tabpost[$post[0]->getCreateAt()->getTimestamp()] = $post[0];
                    }
                    break;

                case "module_shop":
                    $offre=$this->offreRepository->findLastByKey($wb->getCodesite());
                    if($offre){
                        $tabshop[$offre[0]->getCreateAt()->getTimestamp()] = $offre[0];
                    }
                    break;

                case "module_found":
                    $formule = $this->formulesRepository->findlastformuleKey($wb->getCodesite());
                    if($formule) {
                        $tabformule['formule'] = $formule[0];
                        foreach ($formule[0]->getArticles() as $articles) {
                            switch ($articles->getCategorie()->getName()) {
                                case"entree":
                                    $tabformule['entrees'][] = $articles;
                                    break;
                                case"plat":
                                    $tabformule['plats'][] = $articles;
                                    break;
                                case"dessert":
                                    $tabformule['desserts'][] = $articles;
                                    break;
                                case"boisson":
                                    $tabformule['boissons'][] = $articles;
                                    break;
                            }
                        }
                    }
                    break;
            }
        }
        $resa=false;
        // todo rajouter pour market et event
        return ['post'=>$tabpost, 'menu'=>$tabformule, 'resa'=>$resa,'event'=>$tabevent, 'shop'=>$tabshop,];
    }






    /**
     * @param $webnotices
     * @return array
     */
    public function listPublicationsThanDispatch($webnotices): array
    {
        $taball=[];
        $taboffre=[];
        $tabpost=[];
        /** @var membersboard $spw */
        foreach ($webnotices as $spw) {
            /** @var Board $wb */
            $tabwbs[] = $spw['website'];
        }
        if(!empty($tabwbs)) {
            foreach ($tabwbs as $wb) {
                $tabpost = $wb['posts'];
                $taboffre = $wb['offres'];
                if(!empty($tabpost)) {
                    foreach ($tabpost as $post) {
                        if(!empty($post)){
                            $post['website']=$wb;
                            $taball[$post['createAt']->getTimestamp()] = $post;
                        }
                    }
                }
                if(!empty($taboffre)) {
                    foreach ($taboffre as $offre){
                        if(!empty($offre)) {
                            $offre['website']=$wb;
                            $taball[$offre['createAt']->getTimestamp()] = $offre;
                        }
                    }
                }
            }
        }
        krsort($taball);
        return $taball;
    }

    /**
     * @param $key
     * @return array
     */
    public function listPosts($key): array
    {
         return $this->postRepository->ListpostByKey($key);
    }



}