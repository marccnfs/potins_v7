<?php

namespace App\Controller\Game\Escape;

use App\Attribute\RequireParticipant;
use App\Entity\Games\EscapeGame;
use App\Entity\Users\Participant;
use App\Repository\EscapeWorkshopSessionRepository;
use App\Service\Games\EscapeTeamRunAdminService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/escape-team/admin')]
class EscapeTeamAdminController extends AbstractController
{
    #[Route('/new', name: 'escape_team_admin_create', methods: ['GET', 'POST'])]
    #[RequireParticipant]
    public function create(
        Request $request,
        Participant $participant,
        EscapeWorkshopSessionRepository $workshopRepository,
        EscapeTeamRunAdminService $runAdminService,
    ): Response {
        $workshop = $workshopRepository->findOneByCode($participant->getCodeAtelier());
        if (!$workshop || !$workshop->isMaster()) {
            $this->addFlash('danger', 'Cette page est réservée au maître du jeu (session « master »).');

            return $this->redirectToRoute('dashboard_my_escapes');
        }

        $games = $workshop->getEscapeGames()->toArray();
        usort($games, static fn (EscapeGame $a, EscapeGame $b): int => strcmp($a->getTitle() ?? '', $b->getTitle() ?? ''));

        $defaultTitle = $games !== [] ? ($games[0]->getTitle() ?? 'Escape par équipes') : 'Escape par équipes';

        $form = $this->createFormBuilder([
            'title' => $defaultTitle,
            'maxTeams' => 10,
        ])
            ->add('escapeGame', ChoiceType::class, [
                'choices' => $games,
                'choice_value' => 'id',
                'choice_label' => static fn (EscapeGame $game): string => $game->getTitle() ?? sprintf('Escape #%d', $game->getId()),
                'placeholder' => $games === [] ? 'Aucun escape attaché à cette session' : 'Choisis l\'escape à projeter',
                'required' => true,
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre projeté',
                'attr' => ['placeholder' => 'Escape par équipes'],
            ])
            ->add('heroImageUrl', TextType::class, [
                'label' => 'Image de l\'univers (URL)',
                'required' => false,
                'attr' => ['placeholder' => 'https://.../visuel.jpg'],
            ])
            ->add('maxTeams', IntegerType::class, [
                'label' => 'Nombre maximum d\'équipes',
                'attr' => ['min' => 1, 'max' => 10],
            ])
            ->add('timeLimitMinutes', IntegerType::class, [
                'label' => 'Temps limite (minutes)',
                'required' => false,
                'attr' => ['min' => 0],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer et ouvrir les inscriptions',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $escapeGame = $data['escapeGame'] instanceof EscapeGame ? $data['escapeGame'] : null;

            if ($escapeGame === null) {
                $this->addFlash('danger', 'Sélectionne un escape game pour préparer la session.');
            } else {
                $timeLimitMinutes = $data['timeLimitMinutes'] ?? null;
                $timeLimitSeconds = $timeLimitMinutes !== null ? (int) $timeLimitMinutes * 60 : null;

                $run = $runAdminService->prepareRun(
                    escapeGame: $escapeGame,
                    owner: $participant,
                    title: (string) $data['title'],
                    heroImageUrl: $data['heroImageUrl'] ?? null,
                    maxTeams: (int) $data['maxTeams'],
                    timeLimitSeconds: $timeLimitSeconds,
                );

                $runAdminService->openRegistration($run);

                $this->addFlash('success', 'Session équipes créée : les inscriptions sont ouvertes.');

                return $this->redirectToRoute('escape_team_landing', ['slug' => $run->getShareSlug()]);
            }
        }

        return $this->render('pwa/escape/team/admin_create.html.twig', [
            'workshop' => $workshop,
            'games' => $games,
            'form' => $form->createView(),
            'vartwig' => [
                'title' => 'Créer une session escape par équipes',
            ],
        ]);
    }
}
