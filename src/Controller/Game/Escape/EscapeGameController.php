<?php

namespace App\Controller\Game\Escape;

use App\Attribute\RequireParticipant;
use App\Classe\UserSessionTrait;
use App\Entity\Users\Participant;
use App\Lib\Links;
use App\Repository\EscapeGameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;;
use Symfony\Component\Routing\Attribute\Route;


class EscapeGameController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/escape/home', name: 'home')]
    public function home(EscapeGameRepository $repo,Security $security): Response {

        $participant = $this->getParticipantFromSession();

        $featuredGames = $repo->findBy(['published'=>true], ['id'=>'DESC'], 6);
        $recentGames   = $repo->findBy(['published'=>true], ['id'=>'DESC'], 6);

        $user = $security->getUser();
        $myGames = [];
        $lastPlayed = null;

        if ($participant) {
            $myGames = $repo->findBy(['owner'=>$user], ['id'=>'DESC'], 6);
            // si tu stockes le dernier slug joué en session/localStorage côté front, à adapter ici
            // $lastPlayed = ['slug' => 'mon-escape', 'title' => 'Titre'];
        }

        $vartwig=$this->menuNav->templatepotins(
            '_index',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig',[
            'featuredGames'=>$featuredGames,
            'recentGames'=>$recentGames,
            'myGames'=>$myGames,
            'lastPlayed'=>$lastPlayed,
            'replacejs'=>false,
            'directory'=>'landing',
            'vartwig'=>$vartwig,
            'participant'=>$participant,
        ]);

    }

    #[Route('/escape/créer-escapes/exemple', name: 'dashboard_example')]
    #[RequireParticipant]
    public function showExample(Participant $participant): Response
    {
        $vartwig=$this->menuNav->templatepotins(
            '_example',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs' => false,
            'directory' => 'dashboard',
            'vartwig' => $vartwig,
            'participant' => $participant,
        ]);
    }


    #[Route('/docs/workshop', name: 'docs_workshop')]

    public function workshop(): Response
    {
        $participant = $this->getParticipantFromSession();

        $vartwig=$this->menuNav->templatepotins(
            '_workshop',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'directory'=>'docs',
            'participant' => $participant
        ]);

    }

    #[Route('/docs/legal_mentions', name: 'legal_mentions')]

    public function legalMentions(): Response
    {
        $participant = $this->getParticipantFromSession();

        $vartwig=$this->menuNav->templatepotins(
            '_legal_mentions',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'directory'=>'docs',
            'participant' => $participant
        ]);

    }

    #[Route('/docs/privacy', name: 'privacy')]

    public function privacy(): Response
    {
        $participant = $this->getParticipantFromSession();

        $vartwig=$this->menuNav->templatepotins(
            '_privacy',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'directory'=>'docs',
            'participant' => $participant
        ]);

    }


    private function getParticipantFromSession(): ?Participant
    {
        $participantId = $this->requestStack->getSession()->get('participant_id');
        return $participantId ? $this->em->getRepository(Participant::class)->find($participantId) : null;
    }

}
