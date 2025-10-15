<?php


namespace App\Service\Search;


use App\Entity\Module\GpReview;
use App\Entity\Module\ModuleList;
use App\Entity\Module\PostEvent;
use App\Repository\GpReviewRepository;
use App\Repository\RessourcesRepository;
use App\Repository\GpRessourcesRepository;
use App\Repository\OffresRepository;
use App\Repository\PostEventRepository;
use App\Repository\PostRepository;
use App\Repository\BoardRepository;
use Doctrine\ORM\NonUniqueResultException;
use DateTimeImmutable;
use DateTimeInterface;

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
        return ['board'=>[],'posts'=>[],'post'=>$post,'contents'=>[] /*'key'=>$post->getKeymodule(), "msgp"=>$post->getTbmessages()*/];
    }



    public function searchOnePotinAndRessources($id): bool|array
    {
        if(!$post=$this->postRepository->findOnePostAndReviews($id)) return false; // a finir renvoi que les ressources
        return ['board'=>[],'posts'=>[],'post'=>$post,'contents'=>[]];
    }


    public function searchEventWithPostAndBoard($id): bool|array
    {
        $events = $this->postEventRepository->findAllEventsByIdPotin($id);

        if (!$events) {
            return false;
        }

        $grouped = $this->groupEventsByPotin($events);
        $group = $grouped[0] ?? null;

        if (!$group) {
            return false;
        }
        $potin = $group['potin'];
        $primaryEvent = $group['primaryEvent'];
        $keymodule = $primaryEvent->getKeymodule();
        $board = $keymodule ? $this->websiteRepository->findWbByKey($keymodule) : null;

        $content = '';
        if ($potin->getHtmlcontent() && $potin->getHtmlcontent()->getFileblob()) {
            $content = (string) file_get_contents($potin->getHtmlcontent()->getphpPathblob());
        }

        $posts = $this->postRepository->findAllPotinsActivWithOutPotinsId($potin->getId());

        return [
            'events' => $group['events'],
            'board' => $board,
            'posts' => $posts,
            'post' => $potin,
            'content' => $content,
            'key' => $keymodule,
            'eventSummary' => [
                'potin' => $potin,
                'primaryEvent' => $primaryEvent,
                'nextDate' => $group['nextDate'],
            ],
        ];
    }

    public function findLastBeforeWeek(): bool|array
    {
        $events = $this->postEventRepository->findLastBeforeWeek();

        if (!$events) {
            return false;
        }

        $grouped = $this->groupEventsByPotin($events);

        return $grouped !== [] ? $grouped : false;
    }

    /**
     * @param iterable<PostEvent> $events
     * @return array<int, array<string, mixed>>
     */
    private function groupEventsByPotin(iterable $events): array
    {
        $groups = [];

        foreach ($events as $event) {
            if (!$event instanceof PostEvent) {
                continue;
            }

            $potin = $event->getPotin();
            if (!$potin) {
                continue;
            }

            $potinId = $potin->getId();
            if ($potinId === null) {
                continue;
            }

            if (!isset($groups[$potinId])) {
                $groups[$potinId] = [
                    'potin' => $potin,
                    'primaryEvent' => $event,
                    'nextDate' => null,
                    'locations' => [],
                ];
            }

            $locationKey = $event->getLocatemedia()?->getId();
            if ($locationKey === null) {
                $locationKey = 'unassigned';
            }

            if (!isset($groups[$potinId]['locations'][$locationKey])) {
                $groups[$potinId]['locations'][$locationKey] = [
                    'board' => $event->getLocatemedia(),
                    'dates' => [],
                    'events' => [],
                    'nextDate' => null,
                    'bookingEvent' => $event,
                ];
            }

            $dates = $this->computeEventDatesFromEntity($event);
            $location = &$groups[$potinId]['locations'][$locationKey];

            foreach ($dates as $label => $date) {
                $location['dates'][$label] = $date;
            }

            $location['events'][] = $event;

            $dateValues = array_values($dates);
            $firstDate = $dateValues[0] ?? $this->toImmutable($event->getAppointment()?->getStarttime());

            if ($firstDate && (!$location['nextDate'] || $firstDate < $location['nextDate'])) {
                $location['nextDate'] = $firstDate;
                $location['bookingEvent'] = $event;
            }

            $currentNext = $groups[$potinId]['nextDate'];
            if ($firstDate && (!$currentNext || $firstDate < $currentNext)) {
                $groups[$potinId]['nextDate'] = $firstDate;
                $groups[$potinId]['primaryEvent'] = $event;
            }

            unset($location);
        }

        foreach ($groups as &$group) {
            foreach ($group['locations'] as &$location) {
                ksort($location['dates']);
            }
            unset($location);

            $locations = array_values($group['locations']);

            usort($locations, static function (array $a, array $b): int {
                $dateA = $a['nextDate'];
                $dateB = $b['nextDate'];

                if (!$dateA && !$dateB) {
                    return 0;
                }
                if (!$dateA) {
                    return 1;
                }
                if (!$dateB) {
                    return -1;
                }

                return $dateA->getTimestamp() <=> $dateB->getTimestamp();
            });

            $group['locations'] = $locations;
            $group['events'] = $locations;
        }
        unset($group);

        $groupList = array_values($groups);

        usort($groupList, static function (array $a, array $b): int {
            $dateA = $a['nextDate'];
            $dateB = $b['nextDate'];

            if (!$dateA && !$dateB) {
                return 0;
            }
            if (!$dateA) {
                return 1;
            }
            if (!$dateB) {
                return -1;
            }

            return $dateA->getTimestamp() <=> $dateB->getTimestamp();
        });

        return $groupList;
    }

    /**
     * @return array<string, \DateTimeImmutable>
     */
    private function computeEventDatesFromEntity(PostEvent $event): array
    {
        $appointment = $event->getAppointment();
        if (!$appointment || !$appointment->getTabdate()) {
            return [];
        }

        $rawDates = $appointment->getTabdate()->getTabdatejso();
        $dates = [];
        $today = new DateTimeImmutable('today');

        foreach ($rawDates as $day) {
            if (!is_iterable($day)) {
                continue;
            }

            foreach ($day as $value) {
                if (!$value) {
                    continue;
                }

                $parts = explode(',', $value);
                if (count($parts) < 3) {
                    continue;
                }

                $date = DateTimeImmutable::createFromFormat(
                    'Y-m-d H:i:s',
                    sprintf('%04d-%02d-%02d 00:00:00', (int) $parts[0], ((int) $parts[1]) + 1, (int) $parts[2])
                );

                if (!$date || $date < $today) {
                    continue;
                }

                $dates[$date->format('d/m/Y')] = $date;
            }
        }

        ksort($dates);

        return $dates;
    }

    private function toImmutable(?DateTimeInterface $dateTime): ?DateTimeImmutable
    {
        if (!$dateTime) {
            return null;
        }

        if ($dateTime instanceof DateTimeImmutable) {
            return $dateTime;
        }

        /** @var \DateTime $dateTime */
        return DateTimeImmutable::createFromMutable($dateTime);

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
