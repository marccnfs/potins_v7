<?php

namespace App\Controller\Game\Escape;

use App\Classe\UserSessionTrait;
use App\Lib\Links;
use App\Repository\EscapeTeamRepository;
use App\Repository\EscapeTeamRunRepository;
use App\Repository\EscapeTeamSessionRepository;
use App\Service\Games\EscapeTeamAvatarCatalog;
use App\Service\Games\EscapeTeamProgressService;
use App\Service\Games\EscapeTeamRegistrationService;
use App\Service\Games\EscapeTeamRunAdminService;
use App\Service\MobileLinkManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/escape-team')]
class EscapeTeamController extends AbstractController
{

    use UserSessionTrait;


    public function __construct(
        private readonly EscapeTeamRunRepository $runRepository,
        private readonly EscapeTeamRepository $teamRepository,
        private readonly EscapeTeamSessionRepository $sessionRepository,
        private readonly EscapeTeamRegistrationService $registrationService,
        private readonly EscapeTeamProgressService $progressService,
        private readonly EscapeTeamRunAdminService $runAdminService,
        private readonly EscapeTeamAvatarCatalog $avatarCatalog,
        private readonly MobileLinkManager $mobileLinkManager,
    ) {
    }

    #[Route('/{slug}', name: 'escape_team_landing', methods: ['GET'])]
    public function landing(string $slug): Response
    {
        $run = $this->runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();

        $vartwig=$this->menuNav->templatepotins(
            '_index',
            Links::GAMES);

        $registrationUrl = $this->generateUrl('escape_team_register', ['slug' => $run->getShareSlug()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->render('pwa/escape/home.html.twig',[
            'run' => $run,
            'landing' => $this->runAdminService->buildLandingContext($run),
            'teams' => $this->teamRepository->findForRunOrdered($run),
            'progress' => $this->progressService->buildLiveProgress($run),
            'registrationUrl' => $registrationUrl,
            'registrationQr' => $this->mobileLinkManager->buildQrForUrl($registrationUrl),
            'directory'=>'team',
            'template'=>'team/landing.html.twig',
            'vartwig'=>$vartwig,
            'title' => sprintf('Escape par équipes · %s', $run->getTitle()),
        ]);
    }

    #[Route('/{slug}/register', name: 'escape_team_register', methods: ['GET', 'POST'])]
    public function register(Request $request, string $slug): Response
    {
        $run = $this->runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();

        if ($request->isMethod('POST')) {
            $teamName = trim((string) $request->request->get('teamName', ''));
            $avatarKey = (string) $request->request->get('avatarKey', '');
            $membersPayload = $request->request->all('members');
            $members = array_values(array_filter(array_map(static function ($row): array {
                return [
                    'nickname' => trim((string) ($row['nickname'] ?? '')),
                    'avatarKey' => (string) ($row['avatarKey'] ?? ''),
                ];

             }, is_array($membersPayload) ? $membersPayload : []), static function (array $member): bool {
        return $member['nickname'] !== '';
    }));

            try {
                $this->registrationService->registerTeam($run, $teamName, $avatarKey, $members);
                $this->addFlash('success', 'Équipe inscrite, prête pour le départ !');

                return $this->redirectToRoute('escape_team_register', ['slug' => $slug]);
            } catch (\Throwable $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        $vartwig=$this->menuNav->templatepotins(
            '_index',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig',[
            'run' => $run,
            'teams' => $this->teamRepository->findForRunOrdered($run),
            'avatars' => $this->avatarCatalog->all(),
            'isRegistrationOpen' => $run->isRegistrationOpen(),
            'directory'=>'team',
            'template'=>'team/register.html.twig',
            'vartwig'=>$vartwig,
            'title' => sprintf('Inscription équipes · %s', $run->getTitle()),
        ]);

    }

    #[Route('/{slug}/progress', name: 'escape_team_progress', methods: ['GET'])]
    public function progress(string $slug): JsonResponse
    {
        $run = $this->runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();

        return $this->json($this->progressService->buildLiveProgress($run));
    }

    #[Route('/{slug}/leaderboard', name: 'escape_team_leaderboard', methods: ['GET'])]
    public function leaderboard(string $slug): Response
    {
        $run = $this->runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();

        $leaderboard = $this->progressService->computeLeaderboard($run);

        $vartwig=$this->menuNav->templatepotins(
            '_index',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig',[
            'run' => $run,
            'leaderboard' => $leaderboard,
            'directory'=>'team',
            'template'=>'team/leaderboard.html.twig',
            'vartwig'=>$vartwig,
            'title' => sprintf('Classement · %s', $run->getTitle()),
        ]);
    }

    #[Route('/{slug}/live', name: 'escape_team_live', methods: ['GET'])]
    public function live(string $slug): Response
    {
        $run = $this->runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();

        $vartwig=$this->menuNav->templatepotins(
            '_index',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig',[
            'run' => $run,
            'snapshot' => $this->progressService->buildLiveProgress($run),
            'directory'=>'team',
            'template'=>'team/live.html.twig',
            'vartwig'=>$vartwig,
            'title' => sprintf('Live · %s', $run->getTitle()),
        ]);
    }

    #[Route('/{slug}/winner', name: 'escape_team_winner', methods: ['GET'])]
    public function winner(string $slug): Response
    {
        $run = $this->runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();

        $winner = $this->progressService->findWinner($run);

        $vartwig=$this->menuNav->templatepotins(
            '_index',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig',[
            'run' => $run,
            'winner' => $winner,
            'leaderboard' => $this->progressService->computeLeaderboard($run),
            'directory'=>'team',
            'template'=>'team/winner.html.twig',
            'vartwig'=>$vartwig,
            'title' => sprintf('Gagnant · %s', $run->getTitle()),
        ]);
    }

    #[Route('/{slug}/play/{teamId}', name: 'escape_team_play', methods: ['GET'])]
    public function play(string $slug, int $teamId): Response
    {
        $run = $this->runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();
        $team = $this->teamRepository->find($teamId);
        if (!$team || $team->getRun()?->getId() !== $run->getId()) {
            throw $this->createNotFoundException();
        }

        $session = $this->sessionRepository->findOneByTeam($team) ?? null;

        $vartwig=$this->menuNav->templatepotins(
            '_index',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig',[
            'run' => $run,
            'team' => $team,
            'session' => $session,
            'template'=>'team/play.html.twig',
            'vartwig'=>$vartwig,
            'title' => sprintf('Équipe %s · %s', $team->getName(), $run->getTitle()),
        ]);
    }


    #[Route('/{slug}/team/{teamId}/step/{step}', name: 'escape_team_step_complete', methods: ['POST'])]
    public function completeStep(Request $request, string $slug, int $teamId, int $step): JsonResponse
    {
        $run = $this->runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();
        $team = $this->teamRepository->find($teamId);
        if (!$team || $team->getRun()?->getId() !== $run->getId()) {
            throw $this->createNotFoundException();
        }

        $session = $this->sessionRepository->findOneByTeam($team) ?? throw $this->createNotFoundException();

        $durationMs = $request->request->has('durationMs') ? $request->request->getInt('durationMs') : null;
        $hintsDelta = $request->request->getInt('hintsUsedDelta', 0);
        $metadata = (array) $request->request->all('meta');

        $partialKey = trim((string) $request->request->get('partialKey', ''));
        if ($partialKey !== '') {
            $expectedParts = max(1, $request->request->getInt('expectedParts', 3));
            $session = $this->progressService->recordLogicPartCompletion(
                $session,
                $step,
                $partialKey,
                $expectedParts,
                totalSteps: 5,
                metadata: $metadata,
                stepDurationMs: $durationMs,
                hintsUsedDelta: $hintsDelta,
            );
        } else {
            $session = $this->progressService->recordStepCompletion(
                $session,
                $step,
                totalSteps: 5,
                stepDurationMs: $durationMs,
                hintsUsedDelta: $hintsDelta,
                metadata: $metadata,
            );
        }

        return $this->json([
            'sessionId' => $session->getId(),
            'teamId' => $team->getId(),
            'currentStep' => $session->getCurrentStep(),
            'completed' => $session->isCompleted(),
            'stepStates' => $session->getStepStates(),
        ]);
    }

    #[Route('/{slug}/team/{teamId}/hint', name: 'escape_team_consume_hint', methods: ['POST'])]
    public function consumeHint(string $slug, int $teamId, Request $request): JsonResponse
    {
        $run = $this->runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();
        $team = $this->teamRepository->find($teamId);
        if (!$team || $team->getRun()?->getId() !== $run->getId()) {
            throw $this->createNotFoundException();
        }

        $session = $this->sessionRepository->findOneByTeam($team) ?? throw $this->createNotFoundException();
        $count = max(1, $request->request->getInt('count', 1));
        $session = $this->progressService->consumeHint($session, $count);

        return $this->json([
            'sessionId' => $session->getId(),
            'hintsUsed' => $session->getHintsUsed(),
        ]);
    }

    #[Route('/{slug}/team/{teamId}/final', name: 'escape_team_finalize', methods: ['POST'])]
    public function finalize(string $slug, int $teamId, Request $request): JsonResponse
    {
        $run = $this->runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();
        $team = $this->teamRepository->find($teamId);
        if (!$team || $team->getRun()?->getId() !== $run->getId()) {
            throw $this->createNotFoundException();
        }

        $session = $this->sessionRepository->findOneByTeam($team) ?? throw $this->createNotFoundException();

        $finalAnswer = trim((string) $request->request->get('finalAnswer', ''));
        $secretCode = trim((string) $request->request->get('secretCode', ''));

        $session = $this->progressService->submitFinalAnswer($session, $finalAnswer, $secretCode);

        return $this->json([
            'sessionId' => $session->getId(),
            'completed' => $session->isCompleted(),
            'secretCode' => $session->getSecretCode(),
            'finalAnswer' => $session->getFinalAnswer(),
            'endedAt' => $session->getEndedAt(),
        ]);
    }
}
