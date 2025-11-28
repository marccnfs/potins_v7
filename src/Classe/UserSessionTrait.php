<?php
declare(strict_types=1);

namespace App\Classe;

use App\Entity\Boards\Board;
use App\Entity\Customer\Customers;
use App\Entity\Member\Activmember;
use App\Entity\Games\EscapeWorkshopSession;
use App\Entity\Users\Participant;
use App\Entity\Users\User;
use App\Lib\Links;
use App\Repository\CustomersRepository;
use App\Repository\BoardRepository;
use App\Repository\BoardslistRepository;
use App\Repository\ActivMemberRepository;
use App\Repository\EscapeWorkshopSessionRepository;
use App\Repository\UserRepository;
use App\Repository\PostEventRepository;
use App\Service\MenuNavigator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Trait UserSessionTrait (Symfony 7-ready) — v2
 *
 * Ajouts vs v1 :
 * - Méthode publique ensureUserSessionBooted() pour permettre à un EventSubscriber
 *   d'initialiser le contexte sans violer la visibilité protected de bootUserSession().
 */
trait UserSessionTrait
{
    // ===== Dépendances (nullables pour rester tolérant avant boot) ================================
    protected ?RequestStack $requestStack = null;
    protected ?EntityManagerInterface $em = null;
    protected ?Security $security = null;
    protected ?MenuNavigator $menuNav = null;
    protected ?RouterInterface $router = null;
    protected ?LoggerInterface $logger = null;

    // Repositories optionnels (si votre contrôleur en a besoin)
    protected ?CustomersRepository $customersRepo = null;
    protected ?BoardRepository $boardRepo = null;
    protected ?BoardslistRepository $boardslistRepo = null;
    protected ?UserRepository $userRepository = null;
    protected ?ActivMemberRepository $activMemberRepo = null;
    protected ?PostEventRepository $postEventRepo = null;

    // Outils optionnels
    protected ?UploaderHelper $uploaderHelper = null;

    // ===== État courant mis en cache =============================================================
    protected ?Customers $currentCustomer = null;
    protected ?Activmember $currentMember  = null;
    protected ?Board $currentBoard         = null;

    // alias pour compatibilité avec le code existant
    protected ?Customers $customer = null;
    protected ?Activmember $member = null;
    protected ?Board $board = null;

    // Détection d'agent
    protected bool $isMobile = false;
    protected string $agentPrefix = 'desk/';
    protected string $useragentP = 'desk/';

    // ====== Initialisation des dépendances (évite __construct bruyant) ===========================
    #[Required]
    public function initUserSessionDeps(
        RequestStack $requestStack,
        Security $security,
        MenuNavigator $menuNav,
        ?EntityManagerInterface $em = null,
        ?RouterInterface $router = null,
        ?LoggerInterface $logger = null,
        ?UploaderHelper $uploaderHelper = null,
        ?CustomersRepository $customersRepo = null,
        ?BoardRepository $boardRepo = null,
        ?BoardslistRepository $boardslistRepo = null,
        ?UserRepository $userRepository = null,
        ?ActivMemberRepository $activMemberRepo = null,
        ?PostEventRepository $postEventRepo = null,
    ): void {
        $this->requestStack     = $requestStack;
        $this->security         = $security;
        $this->menuNav        = $menuNav;
        $this->em               = $em;
        $this->router           = $router;
        $this->logger           = $logger;
        $this->uploaderHelper   = $uploaderHelper;
        $this->customersRepo    = $customersRepo;
        $this->boardRepo        = $boardRepo;
        $this->boardslistRepo   = $boardslistRepo;
        $this->userRepository   = $userRepository;
        $this->activMemberRepo  = $activMemberRepo;
        $this->postEventRepo    = $postEventRepo;
    }

    // ====== Boot du contexte utilisateur (SAFE / sans exception) ================================
    /**
     * Initialise le "contexte" (customer, board, avatar, agent...) si un utilisateur est disponible.
     * Ne lève JAMAIS d'exception : si pas d'utilisateur, sort simplement.
     */
    protected function bootUserSession(): void
    {
        // Agent (mobile / desk)
        $this->detectAgent();

        // Si pas de token ou pas authentifié (remembered suffit), on sort proprement.
        if (!$this->security?->getToken() || !$this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->currentCustomer = null;
            $this->currentMember   = null;
            $this->currentBoard    = null;
            $this->session()?->remove('idcustomer');
            $this->session()?->remove('idboard');
            $this->session()?->remove('avatar');
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            // Un autre type d'utilisateur (ex: InMemoryUser) : ne rien faire, rester neutre
            $this->currentCustomer = null;
            $this->currentMember   = null;
            $this->currentBoard    = null;
            return;
        }

        // ---- Customer (si présent sur l'entité User) -------------------------------------------
        $customer = null;
        if (\method_exists($user, 'getCustomer')) {
            /** @var Customers|null $customer */
            $customer = $user->getCustomer();
        }

        $this->currentCustomer = $customer;
        $this->currentMember=$customer->getMember();

        if ($customer && \method_exists($customer, 'getId')) {
            $this->session()->set('idcustomer', $customer->getId());
        }

        // ---- Board (si vous avez une relation Customer -> Board) --------------------------------
        $board = null;

        // Exemple 1 : direct $customer->getBoard()
        //if (!$board && $customer && \method_exists($customer, 'getBoard')) {
        //    $board = $customer->getBoard();
        //}

        // Exemple 2 : via un wrapper $customer->getBoardwbcustomer()?->getBoard()
        if (!$board && $customer && \method_exists($customer, 'getBoardwbcustomer')) {
            $wb = $customer->getBoardwbcustomer();
            if ($wb && \method_exists($wb, 'getBoard')) {
                $board = $wb->getBoard();
            }
        }

        //dump($customer,$board);

        if ($board instanceof Board) {
            $this->currentBoard = $board;
            $this->board = $this->currentBoard;
            $this->session()->set('idboard', \method_exists($board, 'getId') ? $board->getId() : null);
        } else {
            $this->currentBoard = null;
            $this->session()->remove('idboard');
        }

        // ---- Avatar (VichUploader) --------------------------------------------------------------
        if ($this->uploaderHelper && \method_exists($user, 'getAvatar') && $user->getAvatar()) {
            // IMPORTANT : passer le nom du CHAMP file (ex: "imageFile"), PAS le mapping
            try {
                $avatarPath = $this->uploaderHelper->asset($user->getAvatar(), 'imageFile');
                if ($avatarPath) {
                    $this->session()->set('avatar', $avatarPath);
                }
            } catch (\Throwable $e) {
                $this->logger?->warning('Avatar asset() failed', ['exception' => $e]);
            }
        }

        // ---- Autres champs courants (ex: email) -------------------------------------------------
        if (\method_exists($user, 'getEmail') && $user->getEmail()) {
            $this->session()->set('mailmember', $user->getEmail());
        }
    }

    /**
     * Proxy public pour permettre à un EventSubscriber d'initialiser le contexte.
     */
    public function ensureUserSessionBooted(): void
    {
        $this->bootUserSession();
    }

    // ====== Helpers =========================================================================

    /** Retourne la Session courante, ou null si indisponible (ex: CLI). */
    protected function session(): ?SessionInterface
    {
        return $this->requestStack?->getSession();
    }

    protected function isMasterParticipant(?Participant $participant, ?EscapeWorkshopSessionRepository $workshopRepository = null): bool
    {
        if (!$participant) {
            return false;
        }

        $repository = $workshopRepository ?? ($this->em?->getRepository(EscapeWorkshopSession::class));

        if (!$repository instanceof EscapeWorkshopSessionRepository) {
            return false;
        }

        return (bool) $repository->findOneByCode($participant->getCodeAtelier())?->isMaster();
    }


    /** Ajoute un flash (silencieux si pas de session). */
    protected function flash(string $type, string $message): void
    {
        $this->session()?->getFlashBag()->add($type, $message);
    }

    /** Retourne l'utilisateur App\Entity\Users\User ou null. */
    protected function getUserOrNull(): ?User
    {
        $u = $this->security?->getUser();
        return $u instanceof User ? $u : null;
    }

    /**
     * Retourne l'utilisateur App\Entity\Users\User ou lève une AccessDeniedException.
     * À utiliser dans une ACTION qui REQUIERT un utilisateur (plutôt que dans le __construct).
     */
    protected function requireUser(): User
    {
        $user = $this->getUserOrNull();
        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }
        return $user;
    }

    // ========= PublicSession: participant courant =========
    /** Lève si l’attribut n’est pas injecté (ex. via un listener #[RequireParticipant]) */
    protected function currentParticipant(Request $request): Participant
    {
        $p = $request->attributes->get('_participant');
        if (!$p instanceof Participant) {
            throw new \LogicException('No participant found - ou #[RequireParticipant] manquant');
        }
        return $p;
    }

    /** Indique si un utilisateur est présent (remembered ou fully). */
    protected function isLoggedIn(): bool
    {
        return (bool) ($this->security?->isGranted('IS_AUTHENTICATED_REMEMBERED'));
    }

    /** Raccourcis pour lire l’état courant initialisé par bootUserSession(). */
    protected function currentCustomer(): ?Customers { return $this->currentCustomer; }
    protected function currentMember(): ?Activmember { return $this->currentMember; }
    protected function currentBoard(): ?Board { return $this->currentBoard; }

    /** Détection naïve de l’agent pour poser 'agent' = 'mobile/' ou 'desk/' en session. */
    protected function detectAgent(): void
    {
        $req = $this->requestStack?->getCurrentRequest();
        if (!$req) {
            $this->isMobile = false;
            $this->agentPrefix = 'desk/';
            $this->useragentP = $this->agentPrefix;
            return;
        }

        $ua = (string) ($req->headers->get('User-Agent') ?? '');
        $isMobile = \preg_match('/Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i', $ua) === 1;

        $this->isMobile   = $isMobile;
        $this->agentPrefix = $isMobile ? 'mobile/' : 'desk/';
        $this->useragentP  = $this->agentPrefix;

        $this->session()?->set('agent', $this->agentPrefix);
    }

    /** Remise à zéro des variables de contexte (utile après logout par ex.). */
    protected function clearUserContext(): void
    {
        foreach (['agent','idcustomer','idboard','avatar','mailmember'] as $k) {
            if ($this->session()?->has($k)) {
                $this->session()->remove($k);
            }
        }
        $this->currentCustomer = null;
        $this->currentMember   = null;
        $this->currentBoard    = null;
    }


    /**
     * @param array<string, mixed> $payload
     */
    private function renderDashboard(string $directory, string $twig, int $nav, array $payload = []): Response
    {
        $board = $this->requireBoard();
        $menuNav = $this->requireMenuNav();

        $vartwig = $menuNav->admin($board, $twig, Links::ADMIN, $nav);

        if ($nav === 1) {
            if (!array_key_exists('contactRequestsCount', $payload)) {
                $payload['contactRequestsCount'] = $this->commentRepository->countAgendaRequests();
            }
        }

        return $this->render($this->agentPrefix . 'ptn_office/home.html.twig', array_merge([
            'directory' => $directory,
            'replacejs' => false,
            'vartwig' => $vartwig,
            'board' => $board,
            'member' => $this->currentMember,
            'customer' => $this->currentCustomer,
        ], $payload));
    }

    private function validateCsrf(string $id, ?string $token): void
    {
        if (!$this->isCsrfTokenValid($id, $token ?? '')) {
            throw new BadRequestHttpException('Jeton de sécurité invalide.');
        }
    }

    private function requireBoard(): Board
    {
        if (!$this->board instanceof Board) {
            throw $this->createNotFoundException('Aucun panneau sélectionné.');
        }

        return $this->board;
    }

    private function requireMenuNav(): MenuNavigator
    {
        if (!$this->menuNav instanceof MenuNavigator) {
            throw new RuntimeException('Le service MenuNavigator est indisponible.');
        }

        return $this->menuNav;
    }

}
