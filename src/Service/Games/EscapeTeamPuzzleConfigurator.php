<?php

namespace App\Service\Games;

use App\Entity\Games\EscapeGame;
use App\Entity\Games\Puzzle;

class EscapeTeamPuzzleConfigurator
{
    /**
     * Préconfigure les 5 épreuves de l'escape par équipe (mode papier + logique + QR).
     * Les solutions sont volontairement en placeholder pour être ajustées par l'animateur.
     */
    public function configureDefaultPuzzles(EscapeGame $game): void
    {
        $this->configureWordInputStep(
            game: $game,
            step: 1,
            title: 'Étape 1 — Mot secret du puzzle papier',
            prompt: 'Résous le puzzle papier et saisis le mot trouvé pour débloquer la suite.',
            solutionPlaceholder: 'MOT_ETAPE_1',
            hints: [
                'Observe les symboles communs : ils indiquent l’ordre de lecture.',
                'Le mot est en majuscules sans accents.',
            ],
            finalClue: 'Fragment final A (conserve-le pour la phrase secrète).'
        );

        $this->configureWordInputStep(
            game: $game,
            step: 2,
            title: 'Étape 2 — Mots fléchés papier',
            prompt: 'Complète la grille papier puis inscris le mot code découvert.',
            solutionPlaceholder: 'MOT_ETAPE_2',
            hints: [
                'Commence par les définitions les plus courtes pour débloquer la grille.',
                'Le mot code se lit en colonne surlignée.',
            ],
            finalClue: 'Fragment final B pour la phrase mystère.'
        );

        $this->configureLogicStep($game);
        $this->configureQrStep($game);

        $this->configureWordInputStep(
            game: $game,
            step: 5,
            title: 'Étape 5 — Puzzle papier final',
            prompt: 'Assemble le puzzle papier et saisis le mot obtenu pour libérer la dernière énigme.',
            solutionPlaceholder: 'MOT_ETAPE_5',
            hints: [
                'Commence par assembler les bords pour accélérer le montage.',
                'Le mot apparaît au centre une fois toutes les pièces placées.',
            ],
            finalClue: 'Fragment final E à conserver.'
        );
    }

    private function configureWordInputStep(
        EscapeGame $game,
        int $step,
        string $title,
        string $prompt,
        string $solutionPlaceholder,
        array $hints,
        string $finalClue
    ): void {
        $puzzle = $game->getOrCreatePuzzleByStep($step, 'cryptex');
        $puzzle->setTitle($title);
        $puzzle->setPrompt($prompt);
        $puzzle->setType('cryptex');
        $puzzle->setReady(true);

        $puzzle->setConfig([
            'title'          => $title,
            'prompt'         => $prompt,
            'solution'       => $solutionPlaceholder,
            'hashMode'       => true,
            'alphabet'       => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'scramble'       => false,
            'autocheck'      => true,
            'successMessage' => 'Mot validé, passez à l’étape suivante !',
            'finalClue'      => $finalClue,
            'hints'          => $this->normalizeHints($hints),
        ]);
    }

    private function configureLogicStep(EscapeGame $game): void
    {
        $puzzle = $game->getOrCreatePuzzleByStep(3, 'logic_form');
        $puzzle->setTitle('Étape 3 — Épreuve logique en 3 parties');
        $puzzle->setPrompt('Validez les trois mini-énigmes logiques pour débloquer le QR.');
        $puzzle->setType('logic_form');
        $puzzle->setReady(true);

        $questions = [
            [
                'label'   => 'Partie 1 — Trouve l’intrus',
                'options' => [
                    ['id' => 'A', 'label' => 'Option A'],
                    ['id' => 'B', 'label' => 'Option B (intrus)'],
                    ['id' => 'C', 'label' => 'Option C'],
                ],
                'solution' => ['must' => ['A', 'C'], 'mustNot' => ['B']],
            ],
            [
                'label'   => 'Partie 2 — Vrai ou faux ?',
                'options' => [
                    ['id' => 'A', 'label' => 'Affirmation vraie'],
                    ['id' => 'B', 'label' => 'Affirmation fausse'],
                ],
                'solution' => ['must' => ['A'], 'mustNot' => ['B']],
            ],
            [
                'label'   => 'Partie 3 — Suite logique',
                'options' => [
                    ['id' => 'A', 'label' => 'Réponse attendue'],
                    ['id' => 'B', 'label' => 'Fausses pistes'],
                    ['id' => 'C', 'label' => 'Autre fausse piste'],
                ],
                'solution' => ['must' => ['A'], 'mustNot' => ['B', 'C']],
            ],
        ];

        $puzzle->setConfig([
            'title'       => 'Triple énigme logique',
            'prompt'      => 'Répondez correctement aux trois parties. Toutes les réponses doivent être exactes pour passer.',
            'questions'   => $questions,
            'okMessage'   => '3/3 validés, rendez-vous à l’étape QR !',
            'failMessage' => 'Il reste des erreurs, vérifiez chaque partie.',
            'finalClue'   => 'Fragment final C pour la phrase secrète.',
            'hints'       => $this->normalizeHints([
                'Commencez par identifier les évidences (intrus, contradiction).',
                'Les bonnes réponses peuvent nécessiter plusieurs cases cochées.',
                'Aucune partie ne peut être ignorée : vérifiez les trois avant de valider.',
            ]),
        ]);
    }

    private function configureQrStep(EscapeGame $game): void
    {
        $puzzle = $game->getOrCreatePuzzleByStep(4, 'qr_geo');
        $puzzle->setTitle('Étape 4 — QR code à scanner');
        $puzzle->setPrompt('Scannez le QR code caché pour valider cette étape.');
        $puzzle->setType('qr_geo');
        $puzzle->setReady(true);

        $puzzle->setConfig([
            'title'            => 'QR caché',
            'prompt'           => 'Localisez et scannez le QR pour débloquer la suite.',
            'mode'             => 'qr_only',
            'qrOnly'           => [
                'validateMessage' => 'QR validé !',
                'noExpiry'        => true,
            ],
            'target'           => ['lat' => null, 'lng' => null],
            'radiusMeters'     => 150,
            'okMessage'        => 'QR validé, passez à l’étape finale !',
            'denyMessage'      => 'QR introuvable ? Vérifiez l’emplacement indiqué.',
            'needHttpsMessage' => 'Activez HTTPS pour utiliser le scanner sécurisé.',
            'finalClue'        => 'Fragment final D à noter.',
            'hints'            => $this->normalizeHints([
                'Cherchez près des zones marquées ou des éléments alignés.',
                'Le QR est placé à hauteur d’œil et reste lisible à distance.',
            ]),
        ]);
    }

    private function normalizeHints(array $hints): array
    {
        $filtered = array_values(array_filter(array_map(static fn ($hint) => trim((string) $hint), $hints)));

        return $filtered ?: ['Indice à définir par l’animateur.'];
    }
}
