<?php


namespace App\Service\Search;


use App\Entity\Module\GpReview;
use App\Entity\Module\PostEvent;
use App\Entity\Posts\Article;
use App\Entity\Posts\Post;
use App\Repository\BoardRepository;
use App\Repository\GpReviewRepository;
use App\Repository\PostEventRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\NonUniqueResultException;
use DateTimeImmutable;
use DateTimeInterface;

class Searchmodule
{
    private PostRepository $postRepository;
    private PostEventRepository $postEventRepository;
    private BoardRepository $websiteRepository;
    private GpReviewRepository $gpReviewRepository;


    public function __construct(
        BoardRepository     $websiteRepository,
        PostEventRepository $postEventRepository,
        PostRepository      $postRepository,
        GpReviewRepository  $gpReviewRepository
    )
    {

        $this->postRepository = $postRepository;
        $this->postEventRepository = $postEventRepository;
        $this->websiteRepository = $websiteRepository;
        $this->gpReviewRepository = $gpReviewRepository;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function searchAllInfoWithReviewsAndRessourcesOfOnePotinId($id): bool|array
    {
        $post = $this->postRepository->findOnePostAndReviews($id);

        if (!$post) {
            return false;
        }
        return [
            'post' => $post,
            'contents' => $this->readHtmlContents($post),
        ];
    }

    public function searchAllPotinsOther(): bool|array
    {
        return $this->postRepository->findAllPost();
    }

    public function searchAllOtherPotinsWithOutThisOne($id): bool|array
    {
        return $this->postRepository->findAllPotinsActivWithOutPotinsId($id);
    }

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
        $post = $this->postRepository->findOnePostAndReviews($id);

        if (!$post) {
            return false;
        }
        return [
            'board' => [],
            'posts' => [],
            'post' => $post,
            'contents' => [],
        ];
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
        $posts = $this->postRepository->findAllPotinsActivWithOutPotinsId($potin->getId());

        return [
            'events' => $group['events'],
            'board' => $board,
            'posts' => $posts,
            'post' => $potin,
            'content' => $this->readFirstHtmlContent($potin),
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
     * @return string[]
     */
    private function readHtmlContents(Post $post): array
    {
        $contents = [];

        foreach ($post->getHtmlcontent() as $article) {
            if (!$article instanceof Article) {
                continue;
            }

            $fileName = $article->getFileblob();
            if (!$fileName) {
                continue;
            }

            $path = $article->getphpPathblob();
            if (!is_string($path) || !is_file($path)) {
                continue;
            }

            $data = file_get_contents($path);
            if ($data !== false) {
                $contents[] = $data;
            }
        }
        return $contents;
    }

    private function readFirstHtmlContent(Post $post): string
    {
        $contents = $this->readHtmlContents($post);

        return $contents[0] ?? '';
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
     * @return array<string, DateTimeImmutable>
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

}
