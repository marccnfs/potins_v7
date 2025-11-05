<?php

namespace App\Controller\MainPublic;

use App\Classe\UserSessionTrait;
use App\Lib\Links;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GamePublicController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/games', name: 'games_home')]
    public function index(): Response
    {
        $vartwig = $this->menuNav->templatepotins(
            'home',
            Links::GAMES,
        );

        $cards = [
            [
                'title' => 'Escape games participatifs',
                'description' => "Résolvez des énigmes en équipe dans des univers inspirés des ateliers Potins.",
                'highlights' => [
                    'Jeux pensés pour être animés en médiathèque ou en atelier collectif.',
                    'Guides pas-à-pas pour préparer chaque session facilement.',
                    'Suivi des équipes et classement automatique à la fin du jeu.',
                ],
                'route' => 'home',
                'routeParameters' => [],
                'cta' => 'Découvrir les escape games',
                'icon' => 'fa-solid fa-puzzle-piece',
            ],
            [
                'title' => 'Expériences en réalité augmentée',
                'description' => "Créez des cartes interactives et des parcours ludiques visibles depuis un smartphone.",
                'highlights' => [
                    'Bibliothèque de scènes MindAR prêtes à l’emploi.',
                    'Partage simplifié via QR code et lien sécurisé.',
                    'Accompagnement pour imaginer vos propres expériences.',
                ],
                'route' => 'ar_intro',
                'routeParameters' => [],
                'cta' => 'Explorer la RA',
                'icon' => 'fa-solid fa-cube',
            ],
        ];

        return $this->render($this->useragentP . 'ptn_public/home.html.twig', [
            'directory' => 'games',
            'replacejs' => false,
            'vartwig' => $vartwig,
            'cards' => $cards,
            'customer' => $this->customer,
        ]);
    }
}
