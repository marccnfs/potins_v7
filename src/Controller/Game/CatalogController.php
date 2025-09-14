<?php
namespace App\Controller\Game;

use App\Attribute\RequireParticipant;
use App\Classe\PublicSession;
use App\Entity\Users\Participant;
use App\Lib\Links;
use App\Repository\EscapeGameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CatalogController extends AbstractController
{
    use PublicSession;

    #[Route('/catalog', name: 'catalog')]
//    #[RequireParticipant]
    public function index(Request $req, EscapeGameRepository $repo): Response
    {
        $participantId = $this->requestStack->getSession()->get('participant_id');
        if ($participantId) {
            $participant = $this->em->getRepository(Participant::class)->find($participantId);
        }else{
            $participant = null;
        }

        $q          = trim((string) $req->query->get('q', ''));
        $difficulty = trim((string) $req->query->get('difficulty', '')); // e.g. easy|medium|hard
        $duration   = trim((string) $req->query->get('duration', ''));   // e.g. short|medium|long
        $sort       = trim((string) $req->query->get('sort', 'new'));    // new|popular|title
        $page       = max(1, (int) $req->query->get('page', 1));
        $perPage    = 9;

        $qb = $repo->createQueryBuilder('e')
            ->andWhere('e.published = :pub')->setParameter('pub', true);

        if ($q !== '') {
            // recherche simple : titre ou univers (si univers est stocké en JSON/array, adapter selon mapping)
            $qb->andWhere('LOWER(e.title) LIKE :q OR LOWER(e.shareSlug) LIKE :q')
                ->setParameter('q', '%'.mb_strtolower($q).'%');
        }

        if ($difficulty !== '') {
            // si ton entité a un champ e.difficulty (string)
            $qb->andWhere('e.difficulty = :d')->setParameter('d', $difficulty);
        }

        if ($duration !== '') {
            // si tu as un champ e.durationMinutes (int)
            if ($duration === 'short')     { $qb->andWhere('e.durationMinutes <= 15'); }
            elseif ($duration === 'medium'){ $qb->andWhere('e.durationMinutes BETWEEN 16 AND 30'); }
            elseif ($duration === 'long')  { $qb->andWhere('e.durationMinutes > 30'); }
        }

        // tri
        switch ($sort) {
            case 'title':   $qb->orderBy('e.title', 'ASC'); break;
          //  case 'popular': $qb->orderBy('e.playsCount', 'DESC'); break; // si tu as un compteur
            default:        $qb->orderBy('e.created_at', 'DESC'); break;
        }

        // pagination
        $qbCount = clone $qb;
        $qbCount
            ->resetDQLPart('orderBy')   // <-- IMPORTANT
            ->resetDQLPart('groupBy')   // au cas où
            ->resetDQLPart('having');   // au cas où



        //$qbCount->select('COUNT(e.id)');
        //$total = (int) $qbCount->getQuery()->getSingleScalarResult();

        /*$games = $qb
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()->getResult();

        $pages = (int) max(1, ceil($total / $perPage));
*/
        $perPage = 9;
        $page    = max(1, (int) $req->query->get('page', 1));

        $qb->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $paginator = new Paginator($qb, true);   // fetchJoinCollection=false si tu fais des gros JOINs
        $total     = count($paginator);          // total propre
        $games     = iterator_to_array($paginator->getIterator());

        $pages     = (int) max(1, ceil($total / $perPage));


        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            '_index',
            0,
            "nocity");

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'directory'=>'catalog',
            'games'   => $games,
            'total'   => $total,
            'page'    => $page,
            'pages'   => $pages,
            'perPage' => $perPage,
            'q'       => $q,
            'difficulty' => $difficulty,
            'duration'   => $duration,
            'sort'       => $sort,
            'participant'=>$participant
        ]);

    }
}
