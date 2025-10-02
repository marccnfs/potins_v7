<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Initialise automatiquement le contexte "user session" pour les contrôleurs
 * qui utilisent le UserSessionTrait (v2) via la méthode publique ensureUserSessionBooted().
 *
 * Écouté sur KernelEvents::CONTROLLER afin d'être exécuté APRÈS la sécurité.
 */
final class UserSessionBootSubscriber implements EventSubscriberInterface
{
    public function __construct(private ?LoggerInterface $logger = null) {}

    public static function getSubscribedEvents(): array
    {
        // Priorité faible => passe après la plupart des autres listeners
        return [
            KernelEvents::CONTROLLER => ['onController', -64],
        ];
    }

    public function onController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $controllerCallable = $event->getController();

        // $controllerCallable peut être soit un objet, soit [objet, 'method']
        $controller = \is_array($controllerCallable) ? $controllerCallable[0] : $controllerCallable;
        if (!\is_object($controller)) {
            return;
        }

        // On ne dépend d'aucune interface spécifique : on check juste la méthode publique attendue
        if (\method_exists($controller, 'ensureUserSessionBooted')) {
            try {
                $controller->ensureUserSessionBooted();
            } catch (\Throwable $e) {
                // On ne casse jamais la requête : on logue seulement
                $this->logger?->warning('ensureUserSessionBooted() failed', [
                    'controller' => $controller::class,
                    'exception'  => $e,
                ]);
            }
        }
    }
}
