<?php
namespace App\Controller\Game\Escape;

use App\Classe\UserSessionTrait;
use App\Entity\Games\PlaySession;
use App\Entity\Users\Participant;
use App\Lib\Links;
use App\Repository\EscapeGameRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/catalog', name: 'catalog')]
    public function index(Request $req, EscapeGameRepository $repo): Response
    {
        $participantId = $this->requestStack->getSession()->get('participant_id');
        if ($participantId) {
            $participant = $this->em->getRepository(Participant::class)->find($participantId);
        }else{
            $participant = null;
        }

        $difficultyLabels = [
            'easy'   => 'Facile',
            'medium' => 'Moyenne',
            'hard'   => 'Difficile',
        ];
        $durationLabels = [
            'short'  => '≤ 15 min',
            'medium' => '16–30 min',
            'long'   => '30+ min',
        ];
        $sortLabels = [
            'new'     => 'Nouveaux',
            'popular' => 'Populaires',
            'title'   => 'Titre A→Z',
        ];

        $q          = trim((string) $req->query->get('q', ''));
        $difficulty = trim((string) $req->query->get('difficulty', ''));
        $duration   = trim((string) $req->query->get('duration', ''));
        $sort       = trim((string) $req->query->get('sort', 'new'));
        $page       = max(1, (int) $req->query->get('page', 1));
        $perPage    = 9;

        if ($difficulty !== '' && !isset($difficultyLabels[$difficulty])) {
            $difficulty = '';
        }
        if ($duration !== '' && !isset($durationLabels[$duration])) {
            $duration = '';
        }
        if (!isset($sortLabels[$sort])) {
            $sort = 'new';
        }


        $qb = $repo->createQueryBuilder('e')
            ->andWhere('e.published = :pub')
            ->setParameter('pub', true);

        if ($q !== '') {
            $qb->andWhere('LOWER(e.title) LIKE :q OR LOWER(e.shareSlug) LIKE :q')
                ->setParameter('q', '%'.mb_strtolower($q).'%');
        }

        if ($difficulty !== '') {
            $qb->andWhere('e.difficulty = :d')->setParameter('d', $difficulty);
        }

        if ($duration !== '') {
            if ($duration === 'short') {
                $qb->andWhere('e.durationMinutes IS NOT NULL AND e.durationMinutes <= :short')
                    ->setParameter('short', 15);
            } elseif ($duration === 'medium') {
                $qb->andWhere('e.durationMinutes BETWEEN :medMin AND :medMax')
                    ->setParameter('medMin', 16)
                    ->setParameter('medMax', 30);
            } elseif ($duration === 'long') {
                $qb->andWhere('e.durationMinutes > :long')->setParameter('long', 30);
            }
        }

        if ($sort === 'popular') {
            $qb->leftJoin('e.sessions', 'sortSessions')
                ->addSelect('COUNT(sortSessions.id) AS HIDDEN sortPlays')
                ->groupBy('e.id')
                ->orderBy('sortPlays', 'DESC')
                ->addOrderBy('e.created_at', 'DESC');
        } elseif ($sort === 'title') {
            $qb->orderBy('LOWER(e.title)', 'ASC');
        } else {
            $qb->orderBy('e.created_at', 'DESC');
        }

        $qb->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $paginator = new Paginator($qb, false);
        $total     = count($paginator);
        $games     = iterator_to_array($paginator->getIterator());

        $gameIds = array_map(static fn($game) => $game->getId(), $games);
        $playsCount = [];
        if ($gameIds) {
            $counts = $this->em->createQueryBuilder()
                ->select('IDENTITY(ps.escapeGame) AS gid, COUNT(ps.id) AS cnt')
                ->from(PlaySession::class, 'ps')
                ->where('ps.escapeGame IN (:ids)')
                ->setParameter('ids', $gameIds)
                ->groupBy('ps.escapeGame')
                ->getQuery()
                ->getArrayResult();
            foreach ($counts as $row) {
                $playsCount[(int) $row['gid']] = (int) $row['cnt'];
            }
        }

        $cards = [];
        foreach ($games as $game) {
            $illustrations = $game->getIllustrations();
            $cover = null;
            if ($illustrations instanceof Collection) {
                $first = $illustrations->first();
                if ($first !== false) {
                    $cover = $first;
                }
            }

            $universe = $game->getUniverse();
            $summary = '';
            if (is_array($universe)) {
                $summary = trim((string)($universe['contexte'] ?? ''));
                if ($summary === '') {
                    $summary = trim((string)($universe['objectif'] ?? ''));
                }
            }
            $summary = preg_replace('/\s+/u', ' ', $summary ?? '') ?? '';
            if ($summary !== '' && mb_strlen($summary) > 160) {
                $summary = rtrim(mb_substr($summary, 0, 157)).'…';
            }

            $owner = $game->getOwner();
            $ownerName = '—';
            if ($owner) {
                $ownerName = $owner->getNickname() ?: ($owner->getPrenom() ?: '—');
            }

            $cards[] = [
                'game'            => $game,
                'cover'           => $cover,
                'playsCount'      => $playsCount[$game->getId()] ?? 0,
                'difficultyLabel' => $difficultyLabels[$game->getDifficulty()] ?? null,
                'durationLabel'   => $game->getDurationMinutes() ? sprintf('%d min', $game->getDurationMinutes()) : null,
                'ownerName'       => $ownerName,
                'summary'         => $summary,
            ];
        }

        $pages = (int) max(1, ceil($total / $perPage));


        $vartwig=$this->menuNav->templatepotins('_index',Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'directory'=>'catalog',
            'cards'      => $cards,
            'total'      => $total,
            'page'       => $page,
            'pages'      => $pages,
            'perPage'    => $perPage,
            'q'          => $q,
            'difficulty' => $difficulty,
            'duration'   => $duration,
            'sort'       => $sort,
            'difficultyLabels' => $difficultyLabels,
            'durationLabels'   => $durationLabels,
            'sortLabels'       => $sortLabels,
            'participant'=>$participant
        ]);

    }
}
