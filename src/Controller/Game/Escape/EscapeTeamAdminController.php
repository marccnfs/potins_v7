<?php

namespace App\Controller\Game\Escape;

use App\Attribute\RequireParticipant;
use App\Classe\UserSessionTrait;
use App\Entity\Games\EscapeTeamRun;
use App\Entity\Users\Participant;
use App\Lib\Links;
use App\Form\EscapeTeamRunType;
use App\Repository\EscapeWorkshopSessionRepository;
use App\Repository\EscapeTeamRunRepository;
use App\Service\Games\EscapeTeamProgressService;
use App\Service\Games\EscapeTeamRunAdminService;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/escape-team/admin')]
class EscapeTeamAdminController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/new', name: 'escape_team_admin_create', methods: ['GET', 'POST'])]
    #[RequireParticipant]
    public function create(
        Request $request,
        Participant $participant,
        EscapeWorkshopSessionRepository $workshopRepository,
        EscapeTeamRunAdminService $runAdminService,
        EscapeTeamRunRepository $runRepository,
    ): Response {
        $workshop = $workshopRepository->findOneByCode($participant->getCodeAtelier());
        if (!$workshop || !$workshop->isMaster()) {
            $this->addFlash('danger', 'Cette page est réservée au maître du jeu (session « master »).');

            return $this->redirectToRoute('dashboard_my_escapes');
        }

        $defaultTitle = 'Escape par équipes';

        $form = $this->createForm(
            EscapeTeamRunType::class,
            [
                'title' => $defaultTitle,
                'maxTeams' => 10,
                'step1Solution' => 'ORIGAMI',
                'step1Hints' => "Observe les symboles communs.\nLe mot est en majuscules.",
                'step2Solution' => 'GALAXIE',
                'step2Hints' => "Complète les flèches les plus courtes en premier.\nLe mot code se lit verticalement.",
                'qrSecretWord' => 'MYSTÈRE',
                'cryptexSolution' => 'VICTOIRE',
                'cryptexHints' => "Les lettres sont liées au thème de l\'atelier.\nLe mot final utilise 8 lettres.",
            ],
            ['submit_label' => 'Créer et ouvrir les inscriptions'],
        );


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $timeLimitMinutes = $data['timeLimitMinutes'] ?? null;
            $timeLimitSeconds = $timeLimitMinutes !== null ? (int) $timeLimitMinutes * 60 : null;
            $heroImagePath = $this->handleHeroUpload($form->get('heroImageFile')->getData());


            $run = $runAdminService->prepareRun(
                escapeGame: null,
                owner: $participant,
                title: (string) $data['title'],
                heroImageUrl: $heroImagePath,
                maxTeams: (int) $data['maxTeams'],
                timeLimitSeconds: $timeLimitSeconds,
                puzzleConfig: $this->buildPuzzleConfig($data),
            );

            $runAdminService->openRegistration($run);

            $this->addFlash('success', 'Session équipes créée : les inscriptions sont ouvertes.');

            return $this->redirectToRoute('escape_team_admin_create', ['created' => $run->getShareSlug()]);
        }

        $createdSlug = (string) $request->query->get('created', '');
        $createdRun = $createdSlug !== '' ? $runRepository->findOneByShareSlug($createdSlug) : null;

        $vartwig=$this->menuNav->templatepotins(
            '_index',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig',[
            'workshop' => $workshop,
            'form' => $form->createView(),
            'directory'=>'team',
            'template'=>'team/admin_create.html.twig',
            'vartwig'=>$vartwig,
            'title' =>"Créer une session escape par équipes",
            'participant'=>$participant,
            'createdRun' => $createdRun,
            'isMasterParticipant' => true,
            'active' => 'escape-team',
        ]);

    }

    #[Route('/list', name: 'escape_team_admin_list', methods: ['GET'])]
    #[RequireParticipant]
    public function list(
        Participant $participant,
        EscapeWorkshopSessionRepository $workshopRepository,
        EscapeTeamRunRepository $runRepository,
        Request $request,
    ): Response {
        $workshop = $workshopRepository->findOneByCode($participant->getCodeAtelier());
        if (!$workshop || !$workshop->isMaster()) {
            $this->addFlash('danger', 'Cette page est réservée au maître du jeu (session « master »).');

            return $this->redirectToRoute('dashboard_my_escapes');
        }

        $runs = $runRepository->findAllForOwner($participant);
        $createdSlug = (string) $request->query->get('created', '');
        $createdRun = $createdSlug !== '' ? $runRepository->findOneByShareSlug($createdSlug) : null;

        $vartwig=$this->menuNav->templatepotins(
            '_index',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig',[
            'directory' => 'team',
            'template' => 'team/admin_list.html.twig',
            'vartwig'=>array_replace($vartwig, ['title' => 'Mes escapes-team']),
            'participant'=>$participant,
            'runs' => $runs,
            'createdRun' => $createdRun,
            'isMasterParticipant' => true,
            'active' => 'escape-team',
        ]);
    }

    #[Route('/{slug}/edit', name: 'escape_team_admin_edit', methods: ['GET', 'POST'])]
    #[RequireParticipant]
    public function edit(
        Request $request,
        Participant $participant,
        EscapeWorkshopSessionRepository $workshopRepository,
        EscapeTeamRunRepository $runRepository,
        EscapeTeamRunAdminService $runAdminService,
        string $slug,
    ): Response {
        $workshop = $workshopRepository->findOneByCode($participant->getCodeAtelier());
        if (!$workshop || !$workshop->isMaster()) {
            $this->addFlash('danger', 'Cette page est réservée au maître du jeu (session « master »).');

            return $this->redirectToRoute('dashboard_my_escapes');
        }

        $run = $runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();

        if ($run->getOwner()?->getId() !== $participant->getId()) {
            throw $this->createAccessDeniedException('Tu ne peux modifier que tes propres sessions.');
        }

        if ($run->getStartedAt() !== null) {
            $this->addFlash('danger', 'Cette session a déjà été lancée et ne peut plus être modifiée.');

            return $this->redirectToRoute('escape_team_admin_list');
        }

        $form = $this->createForm(
            EscapeTeamRunType::class,
            $this->buildFormDataFromRun($run),
            ['submit_label' => 'Enregistrer les modifications'],
        );


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $timeLimitMinutes = $data['timeLimitMinutes'] ?? null;
            $timeLimitSeconds = $timeLimitMinutes !== null ? (int) $timeLimitMinutes * 60 : null;
            $now = new DateTimeImmutable();
            $heroImagePath = $this->handleHeroUpload($form->get('heroImageFile')->getData(), $run->getHeroImageUrl());

            $run->setTitle((string) $data['title'])
                ->setHeroImageUrl($heroImagePath)
                ->setMaxTeams((int) $data['maxTeams'])
                ->setTimeLimitSeconds($timeLimitSeconds)
                ->setPuzzleConfig($this->buildPuzzleConfig($data))
                ->setUpdatedAt($now);

            $runAdminService->openRegistration($run);

            $this->addFlash('success', 'Session mise à jour.');

            return $this->redirectToRoute('escape_team_admin_list');
        }

        $vartwig=$this->menuNav->templatepotins(
            '_index',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig',[
            'workshop' => $workshop,
            'form' => $form->createView(),
            'directory'=>'team',
            'template'=>'team/admin_edit.html.twig',
            'vartwig'=>$vartwig,
            'title' =>"Modifier la session escape par équipes",
            'participant'=>$participant,
            'run' => $run,
            'isMasterParticipant' => true,
            'active' => 'escape-team',
        ]);
    }

    #[Route('/{slug}/delete', name: 'escape_team_admin_delete', methods: ['POST'])]
    #[RequireParticipant]
    public function delete(
        Request $request,
        Participant $participant,
        EscapeWorkshopSessionRepository $workshopRepository,
        EscapeTeamRunRepository $runRepository,
        string $slug,
    ): Response {
        $workshop = $workshopRepository->findOneByCode($participant->getCodeAtelier());
        if (!$workshop || !$workshop->isMaster()) {
            $this->addFlash('danger', 'Cette page est réservée au maître du jeu (session « master »).');

            return $this->redirectToRoute('dashboard_my_escapes');
        }

        $run = $runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();

        if ($run->getOwner()?->getId() !== $participant->getId()) {
            throw $this->createAccessDeniedException("Tu n'es pas autorisé à supprimer cette session.");
        }

        if ($run->getStartedAt() !== null && $run->getStatus() !== EscapeTeamRun::STATUS_STOPPED) {
            $this->addFlash('danger', 'Impossible de supprimer : la session a déjà été lancée.');

            return $this->redirectToRoute('escape_team_admin_list');
        }

        if (!$this->isCsrfTokenValid('delete_escape_team_'.$run->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger','Jeton CSRF invalide.');

            return $this->redirectToRoute('escape_team_admin_list');
        }

        $this->em->remove($run);
        $this->em->flush();

        $this->addFlash('success','Escape-team supprimé avec succès.');

        return $this->redirectToRoute('escape_team_admin_list');
    }


    #[Route('/{slug}/pilot', name: 'escape_team_admin_pilot', methods: ['GET', 'POST'])]
    #[RequireParticipant]
    public function pilot(
        Request $request,
        Participant $participant,
        EscapeWorkshopSessionRepository $workshopRepository,
        EscapeTeamRunRepository $runRepository,
        EscapeTeamRunAdminService $runAdminService,
        EscapeTeamProgressService $progressService,
        string $slug,
    ): Response {
        $workshop = $workshopRepository->findOneByCode($participant->getCodeAtelier());
        if (!$workshop || !$workshop->isMaster()) {
            $this->addFlash('danger', 'Cette page est réservée au maître du jeu (session « master »).');

            return $this->redirectToRoute('dashboard_my_escapes');
        }

        $run = $runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();

        if ($request->isMethod('POST')) {
            if ($request->request->has('action_open')) {
                try {
                    $runAdminService->openRegistration($run);
                    $this->addFlash('success', 'Les inscriptions sont de nouveau ouvertes.');
                } catch (\Throwable $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }

            if ($request->request->has('action_close')) {
                try {
                    $runAdminService->closeRegistration($run);
                    $this->addFlash('success', 'Inscriptions fermées : les équipes sont figées.');
                } catch (\Throwable $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }
            if ($request->request->has('action_launch')) {
                $timeLimitMinutes = $request->request->get('timeLimitMinutes');
                $timeLimitSeconds = $timeLimitMinutes !== null && $timeLimitMinutes !== '' ? (int) $timeLimitMinutes * 60 : null;

                try {
                    $runAdminService->launch($run, $timeLimitSeconds);
                    $this->addFlash('success', 'Le jeu est lancé ! Les inscriptions sont verrouillées.');

                    return $this->redirectToRoute('escape_team_live', ['slug' => $slug]);
                } catch (\Throwable $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }
            if ($request->request->has('action_stop')) {
                try {
                    $runAdminService->stop($run);
                    $this->addFlash('warning', 'Le jeu a été stoppé. Les équipes actives sont renvoyées en attente.');
                } catch (\Throwable $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }
            if ($request->request->has('action_reset')) {
                try {
                    $runAdminService->reset($run);
                    $this->addFlash('success', 'La session a été réinitialisée : toutes les équipes ont été supprimées.');
                } catch (\Throwable $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }

            return $this->redirectToRoute('escape_team_admin_pilot', ['slug' => $slug]);
        }

        $vartwig=$this->menuNav->templatepotins(
            '_index',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig',[
            'run' => $run,
            'snapshot' => $progressService->buildLiveProgress($run),
            'teams' => $run->getTeams(),
            'directory'=>'team',
            'template'=>'team/admin_pilot.html.twig',
            'vartwig'=>array_replace($vartwig, ['title' => sprintf('Pilotage · %s', $run->getTitle())]),
            'participant'=>$participant,
            'isMasterParticipant' => true,
            'active' => 'escape-team',
        ]);
    }

    private function buildFormDataFromRun(EscapeTeamRun $run): array
    {
        $puzzleConfig = $run->getPuzzleConfig()['steps'] ?? [];
        $step1 = $puzzleConfig[1] ?? [];
        $step2 = $puzzleConfig[2] ?? [];
        $qrStep = $puzzleConfig[4] ?? [];
        $cryptex = $puzzleConfig[5] ?? [];

        return [
            'title' => $run->getTitle(),
            'maxTeams' => $run->getMaxTeams(),
            'timeLimitMinutes' => $run->getTimeLimitSeconds() !== null ? (int) ceil($run->getTimeLimitSeconds() / 60) : null,
            'step1Solution' => $step1['solution'] ?? '',
            'step1Hints' => $this->implodeHints($step1['hints'] ?? []),
            'step2Solution' => $step2['solution'] ?? '',
            'step2Hints' => $this->implodeHints($step2['hints'] ?? []),
            'qrSecretWord' => $qrStep['secretWord'] ?? '',
            'cryptexSolution' => $cryptex['solution'] ?? '',
            'cryptexHints' => $this->implodeHints($cryptex['hints'] ?? []),
        ];
    }

    private function implodeHints(array $hints): string
    {
        return implode("\n", array_map(static fn (string $hint): string => trim($hint), $hints));
    }

    /**
     * Construit la configuration des 5 étapes (solutions + indices) pour le run.
     * Les indices sont saisis en texte libre (un par ligne) et convertis en tableau.
     */
    private function buildPuzzleConfig(array $data): array
    {
        $logicQuestions = [
            [
                'label' => 'Épreuve logique 1 — Trouve l’intrus',
                'options' => [
                    ['id' => 'A', 'label' => 'Symbole cercle'],
                    ['id' => 'B', 'label' => 'Symbole carré (intrus)'],
                    ['id' => 'C', 'label' => 'Symbole triangle'],
                ],
                'solution' => ['must' => ['A', 'C'], 'mustNot' => ['B']],
            ],
            [
                'label' => 'Épreuve logique 2 — Vrai ou faux ?',
                'options' => [
                    ['id' => 'A', 'label' => 'La clé est cachée au nord'],
                    ['id' => 'B', 'label' => 'La clé est cachée au sud'],
                ],
                'solution' => ['must' => ['A'], 'mustNot' => ['B']],
            ],
            [
                'label' => 'Épreuve logique 3 — Suite à compléter',
                'options' => [
                    ['id' => 'A', 'label' => 'Réponse attendue'],
                    ['id' => 'B', 'label' => 'Fausses pistes'],
                    ['id' => 'C', 'label' => 'Autre fausse piste'],
                ],
                'solution' => ['must' => ['A'], 'mustNot' => ['B', 'C']],
            ],
        ];

        return [
            'steps' => [
                1 => [
                    'type' => 'text',
                    'title' => 'Étape 1 — Mot ou phrase',
                    'prompt' => 'Résous le support papier (codes, acrostiche…) puis saisis le mot exact.',
                    'solution' => trim((string) ($data['step1Solution'] ?? '')),
                    'hints' => $this->splitHints($data['step1Hints'] ?? null, [
                        'Observe les symboles communs : ils donnent l’ordre de lecture.',
                        'Le mot attendu est en majuscules sans accents.',
                    ]),
                    'successMessage' => 'Bonne réponse, direction l’étape 2 !',
                    'failMessage' => 'Mauvaise réponse, vérifie l’orthographe ou les accents.',
                ],
                2 => [
                    'type' => 'text',
                    'title' => 'Étape 2 — Mot ou phrase',
                    'prompt' => 'Complète la grille papier et saisis le mot découvert (colonne ou diagonale).',
                    'solution' => trim((string) ($data['step2Solution'] ?? '')),
                    'hints' => $this->splitHints($data['step2Hints'] ?? null, [
                        'Commence par les définitions les plus courtes pour débloquer la grille.',
                        'Le mot code se lit surligné sur le support papier.',
                    ]),
                    'successMessage' => 'Validé ! Passe à la triple énigme logique.',
                    'failMessage' => 'Le mot ne correspond pas. Essaie à nouveau.',
                ],
                3 => [
                    'type' => 'logic',
                    'title' => 'Étape 3 — Triple épreuve logique',
                    'prompt' => 'Validez les trois mini-tests logiques pour débloquer le QR.',
                    'questions' => $logicQuestions,
                    'hints' => [
                        'Chaque partie peut avoir plusieurs cases à cocher.',
                        'L’intrus est l’option qui ne partage pas la même propriété.',
                        'Relisez les énoncés : une seule combinaison valide les trois tests.',
                    ],
                    'okMessage' => '3/3 validés, rendez-vous à l’étape QR !',
                    'failMessage' => 'Il reste une erreur dans l’une des parties.',
                ],
                4 => [
                    'type' => 'qr_print',
                    'title' => 'Étape 4 — Trouve le QR caché',
                    'prompt' => 'Repère le QR caché par le maître du jeu et scanne-le pour valider l’étape.',
                    'hints' => [
                        'Le QR a été imprimé lors de la création et caché dans la zone de jeu.',
                        'Une fois scanné, la validation est automatique pour l’équipe.',
                    ],
                    'secretWord' => trim((string) ($data['qrSecretWord'] ?? '')),
                ],
                5 => [
                    'type' => 'cryptex',
                    'title' => 'Étape 5 — Cryptex final',
                    'prompt' => 'Tournez les anneaux pour former le mot secret et révéler la phrase finale.',
                    'solution' => trim((string) ($data['cryptexSolution'] ?? '')),
                    'hints' => $this->splitHints($data['cryptexHints'] ?? null, [
                        'Le mot est lié au thème de la session.',
                        'Utilise les fragments trouvés dans chaque étape pour reconstituer le mot.',
                    ]),
                    'alphabet' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                    'successMessage' => 'Cryptex ouvert ! Saisis la phrase finale auprès du maître du jeu.',
                ],
            ],
        ];
    }

    private function splitHints(?string $raw, array $fallback = []): array
    {
        $items = array_values(array_filter(array_map(static fn (string $line): string => trim($line), explode("\n", (string) ($raw ?? '')))));

        return $items !== [] ? $items : $fallback;
    }


    private function handleHeroUpload(?UploadedFile $file, ?string $previousPath = null): ?string
    {
        if (!$file instanceof UploadedFile) {
            return $previousPath;
        }

        $uploadDir = (string) $this->getParameter('escape_team_hero_dir');
        $publicBase = rtrim((string) $this->getParameter('escape_team_hero_public'), '/');

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg';
        $filename = sprintf('escape-team-%s.%s', bin2hex(random_bytes(4)), $extension);
        $file->move($uploadDir, $filename);

        $publicPath = $publicBase . '/' . $filename;

        if ($previousPath && str_starts_with($previousPath, '/')) {
            $absolutePrevious = rtrim((string) $this->getParameter('kernel.project_dir'), '/') . '/public' . $previousPath;
            if (is_file($absolutePrevious) && $previousPath !== $publicPath) {
                @unlink($absolutePrevious);
            }
        }

        return $publicPath;
    }
}
