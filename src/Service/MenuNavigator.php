<?php
declare(strict_types=1);

namespace App\Service;

/**
 * Construit des contextes (tableaux) destinés à Twig pour différents écrans.
 *
 * Notes de refactorisation :
 * - Plus de state partagé : chaque méthode construit son propre tableau.
 * - Factorisation via baseContext() pour les clés communes.
 * - Accès "safe" à $meta avec valeurs par défaut.
 * - Boucle pour m1..m7 (au lieu de 7 affectations) et clamp de l'index actif.
 * - Typage des paramètres là où c’est raisonnable sans connaître vos entités.
 */
final class MenuNavigator
{
    private array $vartwig;

    public function __construct(){
        $this->vartwig=[];
    }

    /**
     * Construit le contexte pour l’affichage d’un post.
     *
     * @param object $post Doit exposer getTitre(), getSubject(), getAuthor()
     */
    public function postinfoObj(object $post, string $html, array $meta): array
    {
        $ctx = $this->baseContext($meta, $html);

        // On suppose que ces méthodes existent ; sinon, adaptez/typhez avec vos entités.
        $ctx['title']       = method_exists($post, 'getTitre') ? (string) $post->getTitre() : ($ctx['title'] ?? '');
        $ctx['description'] = method_exists($post, 'getSubject') ? (string) $post->getSubject() : ($ctx['description'] ?? '');
        $ctx['author']      = method_exists($post, 'getAuthor') ? (string) $post->getAuthor() : null;

        return $ctx;
    }

    /**
     * Construit le contexte "Potins".
     */
    public function templatePotins(string $html, array $meta): array
    {
        $ctx = $this->baseContext($meta, $html);

        // Cohérence title/titlepage
        $title            = (string)($meta['title'] ?? $ctx['title'] ?? '');
        $ctx['title']     = $title;
        $ctx['titlepage'] = $title;

        // Description explicite si fournie dans $meta
        if (array_key_exists('description', $meta)) {
            $ctx['description'] = (string) $meta['description'];
        }
        return $ctx;
    }

    /**
     * Construit le contexte pour l’admin d’un "board".
     *
     * @param object $board Doit exposer getListmodules(): iterable de modules avec getClassmodule(): string
     * @param int|string $nav Index du menu actif (ex: 1..7)
     */
    public function admin(object $board, string $html, array $meta, int|string $nav): array
    {
        $ctx = $this->templatePotins($html, $meta);

        // Liste des activités
        $ctx['tabActivities'] = [];
        if (method_exists($board, 'getListmodules')) {
            foreach ($board->getListmodules() as $module) {
                $ctx['tabActivities'][] = method_exists($module, 'getClassmodule')
                    ? (string) $module->getClassmodule()
                    : '';
            }
        }

        // Indicateurs m1..m7 avec une boucle
        $active = is_numeric($nav) ? max(1, min(7, (int) $nav)) : 0;
        for ($i = 1; $i <= 7; $i++) {
            $ctx['m' . $i] = ($i === $active);
        }

        return $ctx;
    }

    /**
     * Construit les clés communes du contexte à partir de $meta et du HTML.
     */
    private function baseContext(array $meta, string $html): array
    {
        return [
            'maintwig'    => $html,
            'title'       => (string)($meta['title'] ?? ''),
            'titlepage'   => (string)($meta['titlepage'] ?? ($meta['title'] ?? '')),
            'description' => (string)($meta['description'] ?? ''),
            'linkbar'     => $meta['menu']      ?? [],
            'tagueries'   => $meta['tagueries'] ?? [],
        ];
    }

}
