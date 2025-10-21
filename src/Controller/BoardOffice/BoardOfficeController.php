<?php

declare(strict_types=1);

namespace App\Controller\BoardOffice;


use App\Classe\UserSessionTrait;
use App\Entity\Boards\Board;
use App\Entity\Games\EscapeGame;
use App\Entity\Games\EscapeWorkshopSession;
use App\Form\EscapeWorkshopSessionType;
use App\Lib\Links;
use App\Repository\CommentrdvRepository;
use App\Repository\EscapeGameRepository;
use App\Repository\EscapeWorkshopSessionRepository;
use App\Repository\OffresRepository;
use App\Repository\PostEventRepository;
use App\Repository\PostRepository;
use App\Security\ParticipantProvider;
use App\Service\MenuNavigator;
use App\Service\Search\SearchRessources;
use App\Service\Search\SearchReviews;
use DateTime;
use DateTimeImmutable;
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
use Symfony\Component\Form\FormError;


#[IsGranted(new Expression('is_granted("ROLE_MEMBER") or is_granted("ROLE_SUPER_ADMIN")'))]
#[Route('/board-office')]

class BoardOfficeController extends AbstractController
{
    use UserSessionTrait;

    public function __construct(private readonly CommentrdvRepository $commentRepository)
    {
    }

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

    #[Route('/programmation-potins/nouveau', name: 'module_event_new', methods: ['GET'])]
    public function newEvent(PostRepository $postRepository): Response
    {
        $board = $this->requireBoard();
        $posts = $postRepository->ListpostByKey($board->getCodesite());

        return $this->renderDashboard('event', 'selectpotin', 2, [
            'posts' => $posts,
        ]);
    }

    #[Route('/agenda-cnfs', name: 'module_agenda', methods: ['GET'])]
    public function agenda(Request $request): Response
    {
        $dateParam = $request->query->get('date');
        try {
            $date = $dateParam ? new DateTimeImmutable($dateParam) : new DateTimeImmutable('today');
        } catch (\Exception) {
            $date = new DateTimeImmutable('today');
        }

        return $this->renderDashboard('agenda', 'ospaceagenda', 6, [
            'date' => $date,
            'recentRequests' => $this->commentRepository->findRecentAgendaRequests(),
        ]);
    }

    #[Route('/contacts-agenda', name: 'module_agenda_requests', methods: ['GET'])]
    public function agendaRequests(): Response
    {
        return $this->renderDashboard('agenda', 'requests', 7, [
            'requests' => $this->commentRepository->findAllAgendaRequests(),
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
        $board = $this->requireBoard();
        $escapeGames = $escapeGameRepository->findAllForAdministration($board->getCodesite());


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

    #[Route('/escape-games/{id}/edit', name: 'module_escape_edit', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function editEscapeGame(
        EscapeGame $escapeGame,
        ParticipantProvider $participantProvider
    ): RedirectResponse {
        $this->requireBoard();

        $participant = $escapeGame->getOwner() ?? $escapeGame->getParticipant();
        if (!$participant) {
            $this->addFlash('danger', sprintf('Impossible de modifier « %s » : aucun créateur associé.', $escapeGame->getTitle()));

            return $this->redirectToRoute('module_escape_admin');
        }

        $participantProvider->setCurrent($participant);

        return $this->redirectToRoute('wizard_overview', ['id' => $escapeGame->getId()]);
    }

    #[Route('/escape-workshops', name: 'module_escape_workshops', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function escapeWorkshops(
        Request $request,
        EscapeWorkshopSessionRepository $sessionRepository,
        PostEventRepository $eventRepository,
        EscapeGameRepository $escapeGameRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $board = $this->requireBoard();

        $availableEvents = $eventRepository->findEscapeGameWorkshops($board->getCodesite());
        $session = new EscapeWorkshopSession();

        $form = $this->createForm(EscapeWorkshopSessionType::class, $session, [
            'escape_events' => $availableEvents,
        ]);

        $form->handleRequest($request);

        $candidateCode = null;

        if ($form->isSubmitted()) {
            if (!$session->isMaster() && !$session->getEvent()) {
                $form->get('event')->addError(new FormError('Sélectionnez un atelier « escape game » pour créer une session.'));
            }

            $customCode = strtoupper(trim((string) $form->get('customCode')->getData()));
            if ($session->isMaster()) {
                $candidateCode = $customCode !== '' ? $customCode : 'MASTER';
                if ($sessionRepository->existsCode($candidateCode)) {
                    $form->get('customCode')->addError(new FormError('Ce code est déjà utilisé.'));
                }
            } else {
                if ($customCode !== '') {
                    if (!preg_match('/^\d{4}$/', $customCode)) {
                        $form->get('customCode')->addError(new FormError('Le code doit contenir exactement 4 chiffres.'));
                    } elseif ($sessionRepository->existsCode($customCode)) {
                        $form->get('customCode')->addError(new FormError('Ce code est déjà utilisé.'));
                    } else {
                        $candidateCode = $customCode;
                    }
                }

                if ($candidateCode === null) {
                    $candidateCode = $sessionRepository->generateUniqueCode();
                }
            }

            if ($form->isValid()) {
                $session->setCode($candidateCode ?? $sessionRepository->generateUniqueCode());
                if ($session->isMaster() && !$form->get('label')->getData()) {
                    $session->setLabel('Code maître escape game');
                }

                $entityManager->persist($session);
                $entityManager->flush();

                $this->addFlash('success', sprintf('Session « %s » créée avec le code %s.', $session->getDisplayName(), $session->getCode()));

                return $this->redirectToRoute('module_escape_workshops');
            }
        }

        $sessions = $sessionRepository->findAllWithRelations($board->getCodesite());
        $legacyGames = $escapeGameRepository->createQueryBuilder('legacy')
            ->leftJoin('legacy.owner', 'legacyOwner')->addSelect('legacyOwner')
            ->leftJoin('legacy.participant', 'legacyParticipant')->addSelect('legacyParticipant')
            ->andWhere('legacy.workshopSession IS NULL')
            ->orderBy('legacy.created_at', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->renderDashboard('escape', 'ospaceworkshops', 6, [
            'form' => $form->createView(),
            'sessions' => $sessions,
            'legacyGames' => $legacyGames,
        ]);
    }

    #[Route('/escape-games/{id}/attach-session', name: 'module_escape_attach_session', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function attachEscapeToWorkshop(
        EscapeGame $escapeGame,
        Request $request,
        EscapeWorkshopSessionRepository $sessionRepository,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $board = $this->requireBoard();

        $this->validateCsrf('attach_escape_workshop_' . $escapeGame->getId(), $request->request->get('_token'));

        $sessionId = $request->request->getInt('workshop_session_id');
        if ($sessionId <= 0) {
            $this->addFlash('danger', 'Sélectionnez une session valide pour rattacher cet escape game.');

            return $this->redirectToRoute('module_escape_workshops');
        }

        $session = $sessionRepository->find($sessionId);
        if (!$session) {
            throw new BadRequestHttpException('Session introuvable.');
        }

        if (!$session->isMaster() && $session->getEvent()?->getKeymodule() !== $board->getCodesite()) {
            throw $this->createAccessDeniedException('Cette session n’appartient pas à votre tableau de bord.');
        }

        $session->addEscapeGame($escapeGame);
        $session->touch();
        $entityManager->flush();

        $this->addFlash('success', sprintf('« %s » est maintenant rattaché à la session « %s » (%s).', $escapeGame->getTitle(), $session->getDisplayName(), $session->getCode()));

        return $this->redirectToRoute('module_escape_workshops');
    }


    #[Route('/escape-workshops/{id}/delete', name: 'module_escape_workshops_delete', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function deleteEscapeWorkshop(EscapeWorkshopSession $session, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->validateCsrf('delete_escape_workshop_' . $session->getId(), $request->request->get('_token'));

        $label = $session->getDisplayName();

        $entityManager->remove($session);
        $entityManager->flush();

        $this->addFlash('success', sprintf('La session « %s » a été supprimée.', $label));

        return $this->redirectToRoute('module_escape_workshops');
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

}
