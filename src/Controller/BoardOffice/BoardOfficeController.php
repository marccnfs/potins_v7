<?php

declare(strict_types=1);

namespace App\Controller\BoardOffice;


use App\Classe\UserSessionTrait;
use App\Entity\Boards\Board;
use App\Entity\Games\EscapeGame;
use App\Lib\Links;
use App\Repository\EscapeGameRepository;
use App\Repository\OffresRepository;
use App\Repository\PostEventRepository;
use App\Repository\PostRepository;
use App\Service\MenuNavigator;
use App\Service\Search\SearchRessources;
use App\Service\Search\SearchReviews;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted(new Expression('is_granted("ROLE_MEMBER") or is_granted("ROLE_SUPER_ADMIN")'))]
#[Route('/board-office')]

class BoardOfficeController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/tableau-de-bord', name: 'office_member')]
    public function dashboard(PostRepository $postRepository): Response
    {
        $board = $this->requireBoard();
        $posts = $postRepository->findPstKey($board->getCodesite());

        return $this->renderDashboard('potins', 'ospaceblog', 1, [
            'posts' => $posts,
        ]);
    }


    #[Route('/programmation-potins', name: 'module_event')]
    public function events(PostEventRepository $eventRepository): Response
    {
        $board = $this->requireBoard();
        $events = $eventRepository->findEventKey($board->getCodesite());

        return $this->renderDashboard('event', 'ospaceevent', 2, [
            'events' => $events,
            'locatecity' => 0,
        ]);
    }


    #[Route('/offres-potins', name: 'module_offre')]
    public function offers(OffresRepository $offresRepository): Response
    {
        $board = $this->requireBoard();
        $offres = $offresRepository->findOffreKey($board->getCodesite());

        return $this->renderDashboard('offre', 'ospaceoffre', 3, [
            'offres' => $offres,
            'locatecity' => 0,
        ]);
    }


    #[Route('/list-ressources', name: 'module_ressources')]
    public function resources(SearchRessources $searchRessources): Response
    {
        $board = $this->requireBoard();

        return $this->renderDashboard('ressources', 'ospaceressources', 4, [
            'website' => $board,
            'articles' => $searchRessources->findAllRessources(),
            'locatecity' => 0,
        ]);
    }

    #[Route('/list-reviews', name: 'module_reviews')]
    public function reviews(SearchReviews $searchReviews): Response
    {
        $tabreviews=$searchReviews->findAllReviews();

        return $this->renderDashboard('review', 'ospacegpreviews', 4, [
            'reviews' => $searchReviews->findAllReviews(),
        ]);
    }

    #[Route('/escape-games', name: 'module_escape_admin', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function escapeGames(EscapeGameRepository $escapeGameRepository): Response
    {
        $escapeGames = $escapeGameRepository->findAllForAdministration();

        $published = 0;
        foreach ($escapeGames as $escapeGame) {
            if ($escapeGame instanceof EscapeGame && $escapeGame->isPublished()) {
                ++$published;
            }
        }
        $total = \count($escapeGames);

        return $this->renderDashboard('escape', 'ospaceescape', 5, [
            'escapeGames' => $escapeGames,
            'escapeStats' => [
                'total' => $total,
                'published' => $published,
                'draft' => max(0, $total - $published),
            ],
        ]);
    }

    #[Route('/escape-games/{id}/status', name: 'module_escape_status', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function updateEscapeStatus(EscapeGame $escapeGame, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->validateCsrf('status_escape_' . $escapeGame->getId(), $request->request->get('_token'));

        $target = $request->request->get('target_status');
        if (!\in_array($target, ['publish', 'unpublish'], true)) {
            throw new BadRequestHttpException('Action de publication inconnue.');
        }

        $escapeGame->setPublished($target === 'publish');
        $escapeGame->setDatemajAt(new DateTime());

        $entityManager->flush();

        $message = $target === 'publish'
            ? sprintf('« %s » est maintenant publié.', $escapeGame->getTitle())
            : sprintf('« %s » a été retiré de la publication.', $escapeGame->getTitle());

        $this->addFlash('success', $message);

        return $this->redirectToRoute('module_escape_admin');
    }

    #[Route('/escape-games/{id}/delete', name: 'module_escape_delete', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function deleteEscapeGame(EscapeGame $escapeGame, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->validateCsrf('delete_escape_' . $escapeGame->getId(), $request->request->get('_token'));

        $title = $escapeGame->getTitle();
        $entityManager->remove($escapeGame);
        $entityManager->flush();

        $this->addFlash('success', sprintf('L\'escape game « %s » a été supprimé.', $title));

        return $this->redirectToRoute('module_escape_admin');
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function renderDashboard(string $directory, string $twig, int $nav, array $payload = []): Response
    {
        $board = $this->requireBoard();
        $menuNav = $this->requireMenuNav();

        $vartwig = $menuNav->admin($board, $twig, Links::ADMIN, $nav);

        return $this->render($this->agentPrefix . 'ptn_office/home.html.twig', array_merge([
            'directory' => $directory,
            'replacejs' => false,
            'vartwig' => $vartwig,
            'board' => $board,
            'member' => $this->currentMember,
            'customer' => $this->currentCustomer,
        ], $payload));
    }

    private function validateCsrf(string $id, ?string $token): void
    {
        if (!$this->isCsrfTokenValid($id, $token ?? '')) {
            throw new BadRequestHttpException('Jeton de sécurité invalide.');
        }
    }

    private function requireBoard(): Board
    {
        if (!$this->board instanceof Board) {
            throw $this->createNotFoundException('Aucun panneau sélectionné.');
        }

        return $this->board;
    }

    private function requireMenuNav(): MenuNavigator
    {
        if (!$this->menuNav instanceof MenuNavigator) {
            throw new RuntimeException('Le service MenuNavigator est indisponible.');
        }

        return $this->menuNav;
    }


}
