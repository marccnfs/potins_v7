<?php

namespace App\Controller\Game\Escape;

use App\Attribute\RequireParticipant;
use App\Classe\UserSessionTrait;
use App\Entity\Games\EscapeGame;
use App\Entity\Users\Participant;
use App\Lib\Links;
use App\Repository\PlaySessionRepository;
use App\Repository\EscapeWorkshopSessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardParticipantController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/escape/mes-parties', name: 'dashboard_my_sessions')]
    #[RequireParticipant]
    public function listSessions(
        Participant                     $participant,
        PlaySessionRepository           $playSessionRepository,
        EscapeWorkshopSessionRepository $workshopRepository,
    ): Response
    {

        $sessions = $playSessionRepository->findAllForParticipant($participant);
        $byGame = [];
        $participant = $this->getParticipantFromSession();

        foreach ($sessions as $session) {
            $game = $session->getEscapeGame();
            if (!$game) {
                continue;
            }

            $gid = $game->getId();
            if (!isset($byGame[$gid])) {
                $byGame[$gid] = [
                    'game'           => $game,
                    'sessions'       => [],
                    'active'         => null,
                    'best'           => null,
                    'totalSteps'     => max(1, $game->getPuzzles()->count() ?: 6),
                    'resumeStep'     => 1,
                    'completedCount' => 0,
                ];
            }

            $byGame[$gid]['sessions'][] = $session;

            if (!$session->isCompleted() && !$byGame[$gid]['active']) {
                $byGame[$gid]['active'] = $session;
            }

            if ($session->isCompleted()) {
                $byGame[$gid]['completedCount']++;
                $best = $byGame[$gid]['best'];
                if (!$best || $session->getScore() > $best->getScore()) {
                    $byGame[$gid]['best'] = $session;
                }
            }
        }

        foreach ($byGame as $gid => $row) {
            if ($row['active']) {
                $byGame[$gid]['resumeStep'] = $row['active']->getResumeStep($row['totalSteps']);
            }
        }

        $gamesSessions = array_values($byGame);

        $vartwig=$this->menuNav->templatepotins(
            '_sessions',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'directory'=>'dashboard',
            'vartwig'=>$vartwig,
            'participant' => $participant,
            'gamesSessions' => $gamesSessions,
            'active' => 'sessions',
            'isMasterParticipant' => $this->isMasterParticipant($participant, $workshopRepository),
        ]);

    }


    #[Route('/escape/mes-escapes', name: 'dashboard_my_escapes')]
    #[RequireParticipant]
    public function listEscapeGame(
        Participant $participant,
        EscapeWorkshopSessionRepository $workshopRepository,
    ): Response
    {

        $games=$participant->getEscapeGames();

        $vartwig=$this->menuNav->templatepotins(
            '_liste',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'directory'=>'dashboard',
            'vartwig'=>$vartwig,
            'participant' => $participant,
            'progression'=>0,
            'games'=>$games,
            'isMasterParticipant' => $this->isMasterParticipant($participant, $workshopRepository),
        ]);

    }

    #[Route('/escape/{id}/delete', name: 'escape_delete', methods: ['POST'])]
    #[RequireParticipant]
    public function delete(Request $req,EscapeGame $eg): Response {

        $participant = $req->attributes->get('_participant');

        // Sécurité : seul le créateur peut supprimer
        if (!$eg->getOwner() || $eg->getOwner()->getId() !== $participant->getId()) {
            throw $this->createAccessDeniedException("Tu n'es pas autorisé à supprimer ce jeu.");
        }

        // Vérifie le CSRF
        if (!$this->isCsrfTokenValid('delete_escape_'.$eg->getId(), $req->request->get('_token'))) {
            $this->addFlash('danger','Jeton CSRF invalide.');
            return $this->redirectToRoute('dashboard_my_escapes');
        }

        // (Optionnel) supprimer les fichiers images liés aux puzzles
        foreach ($eg->getPuzzles() as $puzzle) {
            $cfg = $puzzle->getConfig();
            if (isset($cfg['imagePath'])) {
                $abs = $this->getParameter('kernel.project_dir').'/public'.$cfg['imagePath'];
                if (is_file($abs)) { @unlink($abs); }
            }
        }

        $this->em->remove($eg);
        $this->em->flush();

        $this->addFlash('success','Escape supprimé avec succès.');
        return $this->redirectToRoute('dashboard_my_escapes');
    }


    private function getParticipantFromSession(): ?Participant
    {
        $participantId = $this->requestStack->getSession()->get('participant_id');
        return $participantId ? $this->em->getRepository(Participant::class)->find($participantId) : null;
    }

    private function isMasterParticipant(Participant $participant, EscapeWorkshopSessionRepository $workshopRepository): bool
    {
        return (bool) $workshopRepository->findOneByCode($participant->getCodeAtelier())?->isMaster();
    }

}
