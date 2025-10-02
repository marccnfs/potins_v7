<?php
declare(strict_types=1);

namespace App\Classe;

use App\Entity\Boards\Board;
use App\Entity\Customer\Customers;
use App\Entity\Member\Activmember;
use App\Entity\Member\Boardslist;
use App\Entity\Users\Participant;
use App\Entity\Users\User;

use App\Repository\ActivMemberRepository;
use App\Repository\BoardRepository;
use App\Repository\BoardslistRepository;
use App\Repository\CustomersRepository;
use App\Repository\PostEventRepository;
use App\Repository\UserRepository;

use App\Service\MenuNavigator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

trait UserSessionTraitOld
{
    // ————— Dépendances (nullables tant que non bootées) —————
    protected ?RequestStack $requestStack = null;
    protected ?EntityManagerInterface $em = null;
    protected ?MenuNavigator $menuNav = null;
    protected ?Security $security = null;

    protected ?BoardRepository $boardRepo = null;
    protected ?BoardslistRepository $boardslistRepo = null;
    protected ?CustomersRepository $customersRepo = null;
    protected ?UserRepository $userRepository = null;
    protected ?ActivMemberRepository $activMemberRepo = null;
    protected ?PostEventRepository $postEventRepo = null;
    protected ?RouterInterface $router = null;

    protected ?UploaderHelper $helper = null; // Vich helper

    // ————— Cache courant + alias compatibles contrôleurs —————
    protected ?Customers $currentCustomer = null;
    protected ?Activmember $currentMember = null;
    protected ?Board $currentBoard = null;

    // alias pour compatibilité avec le code existant
    protected ?Customers $customer = null;
    protected ?Activmember $member = null;
    protected ?Board $board = null;

    // ————— Infos agent —————
    protected bool $isMobile = false;
    protected string $agentPrefix = 'desk/';
    protected string $useragentP = 'desk/'; // utilisé dans tes renders

    // ========= Boot (appelé par le setter #[Required] ci-dessous OU par un __construct) =========
    protected function bootUserSession(
        RequestStack $requestStack,
        EntityManagerInterface $em,
        MenuNavigator $menuNav,
        Security $security,
        ?UploaderHelper $helper = null,
        ?BoardRepository $boardRepo = null,
        ?BoardslistRepository $boardslistRepo = null,
        ?CustomersRepository $customersRepo = null,
        ?UserRepository $userRepository = null,
        ?ActivMemberRepository $activMemberRepo = null,
        ?PostEventRepository $postEventRepo = null,
        ?RouterInterface $router = null,
    ): void {
        $this->requestStack   = $requestStack;
        $this->em             = $em;
        $this->menuNav        = $menuNav;
        $this->security       = $security;
        $this->helper         = $helper;

        $this->boardRepo      = $boardRepo;
        $this->boardslistRepo = $boardslistRepo;
        $this->customersRepo  = $customersRepo;
        $this->userRepository = $userRepository;
        $this->activMemberRepo= $activMemberRepo;
        $this->postEventRepo  = $postEventRepo;
        $this->router         = $router;

        $this->detectAgent();
    }

    // ========= Setter autowiré (évite d’écrire des __construct partout) =========
    #[Required]
    public function initUserSessionDeps(
        RequestStack $requestStack,
        EntityManagerInterface $em,
        MenuNavigator $menuNav,
        Security $security,
        ?UploaderHelper $helper = null,
        ?CustomersRepository $customersRepo = null,
    ): void {
        $this->bootUserSession(
            requestStack: $requestStack,
            em: $em,
            menuNav: $menuNav,
            security: $security,
            helper: $helper,
            customersRepo: $customersRepo,
        );
    }

    // ========= Helpers bas niveau =========
    protected function session(): SessionInterface
    {
        if (!$this->requestStack) {
            throw new \LogicException('UserSessionTrait non initialisé. (bootUserSession() non appelé)');
        }
        $session = $this->requestStack->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }
        return $session;
    }

    protected function detectAgent(?Request $request = null): void
    {
        $req = $request ?? $this->requestStack?->getCurrentRequest();
        $ua  = $req?->headers->get('User-Agent') ?? ($_SERVER['HTTP_USER_AGENT'] ?? '');

        if (\preg_match('/mob/i', $ua)) {
            $this->isMobile    = true;
            $this->agentPrefix = 'mobile/';
        } else {
            $this->isMobile    = false;
            $this->agentPrefix = 'desk/';
        }
        $this->useragentP = $this->agentPrefix;
        $this->session()->set('agent', $this->agentPrefix);
    }

    public function clearinit(): void
    {
        foreach (['city','idcustomer','lat','lon','iddisptachweb','permission','idcity','typeuser','mailmember','idboard','avatar'] as $k) {
            if ($this->session()->has($k)) $this->session()->remove($k);
        }
        $this->currentCustomer = $this->customer = null;
        $this->currentMember   = $this->member   = null;
        $this->currentBoard    = $this->board    = null;
    }


    // ========= Public session (contexte Twig public de base) =========
    public function publicSession(array $meta = [], string $html = ''): array
    {
        return [
            'agent'       => $this->agentPrefix,
            'isMobile'    => $this->isMobile,
            'permission'  => $this->session()->get('permission', null),
            'idcity'      => $this->session()->get('idcity', null),

            'title'       => (string)($meta['title'] ?? ''),
            'titlepage'   => (string)($meta['titlepage'] ?? ($meta['title'] ?? '')),
            'description' => (string)($meta['description'] ?? ''),
            'maintwig'    => $html,
            'linkbar'     => $meta['menu']      ?? [],
            'tagueries'   => $meta['tagueries'] ?? [],
        ];
    }

    // ========= User/member/customer =========
    public function userSession(): void
    {
        $user = $this->security?->getUser();
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException();
        }
        if ($user instanceof User) {
            //$scutomer=$this->customersRepo->find($user->getCustomer()->getId());
            $this->currentCustomer = $user->getCustomer();
            $this->customer        = $this->currentCustomer;

            // côté Customers, ton getMember() existe
            $this->currentMember   = $this->currentCustomer?->getMember();
            $this->member          = $this->currentMember;
        }
    }

    /** Charge le client courant depuis la session 'idcustomer', sinon via userSession() */
    public function customerSession(): ?Customers
    {
        if ($this->currentCustomer instanceof Customers) return $this->currentCustomer;

        $id = $this->session()->get('idcustomer');
        if ($id && $this->customersRepo) {
            $this->currentCustomer = $this->customersRepo->find($id);
            $this->customer        = $this->currentCustomer;
            return $this->currentCustomer;
        }

        // fallback : utilisateur connecté → customer
        $this->userSession();
        return $this->currentCustomer;
    }

    /** Résout/retourne le membre courant si possible (via session mail ou user) */
    public function memberSession(): ?Activmember
    {
        if ($this->currentMember instanceof Activmember) return $this->currentMember;

        // si déjà chargé par userSession()
        $this->userSession();
        if ($this->currentMember instanceof Activmember) return $this->currentMember;

        // sinon via repo + email si nécessaire (à adapter si tu veux)
        return null;
    }

    // ========= Admin-like helpers =========
    public function withAdminNavFlags(array $ctx, int|string $nav): array
    {
        $active = \is_numeric($nav) ? \max(1, \min(7, (int)$nav)) : 0;
        for ($i = 1; $i <= 7; $i++) {
            $ctx['m'.$i] = ($i === $active);
        }
        return $ctx;
    }

    public function resolveCurrentBoard(): ?Board
    {
        if ($this->currentBoard instanceof Board) {
            // aligne l’alias
            $this->board = $this->currentBoard;
            return $this->currentBoard;
        }

        // 1) via session
        $s = $this->session();
        if ($s->has('idboard') && $this->boardRepo) {
            $b = $this->boardRepo->find((int)$s->get('idboard'));
            if ($b instanceof Board) {
                $this->currentBoard = $b;
                $this->board = $b;
                return $b;
            }
        }

        // 2) via customer → wbCustomer → board
        $this->customerSession();
        $this->initBoard(); // sécurisée, pose aussi $this->currentBoard/$this->board si trouvé

        return $this->currentBoard;
    }

    /** Raccourci : récupérer une Boardslist "admin site" (id par défaut 4, ajuste selon ton domaine) */
    public function getAdminPwSite(int $id = 4): ?Boardslist
    {
        return $this->boardslistRepo?->find($id);
    }

    /** Compat: méthode utilitaire retrouvée dans tes traits */
    public function getDispatchByEmail(string $email): mixed
    {
        if (!$this->activMemberRepo) return null;
        return \method_exists($this->activMemberRepo, 'finddispatchmail')
            ? $this->activMemberRepo->finddispatchmail($email)
            : $this->activMemberRepo->findOneBy(['email' => $email]);
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

    // ========= Setters pratiques sur la session =========
    public function setTypeUser(?string $type): void
    {
        $s = $this->session();
        if ($type === null) { $s->remove('typeuser'); return; }
        $s->set('typeuser', $type);
    }

    /** Ecrit le mail membre en session (utilisé par memberSession) */
    public function setMemberEmail(?string $email): void
    {
        $s = $this->session();
        if ($email === null || $email === '') {
            if ($s->has('mailmember')) $s->remove('mailmember');
            $this->currentMember = null;
            return;
        }
        $s->set('mailmember', $email);
        $this->currentMember = null; // force rechargement
    }

    public function setCustomerId(?int $id): void
    {
        $s = $this->session();
        if ($id === null) { $s->remove('idcustomer'); $this->currentCustomer = $this->customer = null; return; }
        $s->set('idcustomer', $id);
        $this->currentCustomer = $this->customer = null; // force reload
    }

    public function setBoardId(?int $id): void
    {
        $s = $this->session();
        if ($id === null) { $s->remove('idboard'); $this->currentBoard = $this->board = null; return; }
        $s->set('idboard', $id);
        $this->currentBoard = $this->board = null; // force reload
    }

    // ========= Initialisation du board + avatar (sécurisée) =========
    protected function initBoard(): void
    {
        // Customer peut être null
        $customer = $this->currentCustomer ?? $this->customer ?? null;
        if (!$customer instanceof Customers) {
            return;
        }

        // Profil / Avatar optionnels
        $profil = \method_exists($customer, 'getProfil') ? $customer->getProfil() : null;
        $avatar = $profil && \method_exists($profil, 'getAvatar') ? $profil->getAvatar() : null;

        // 👉 Vich helper présent + avatar + mapping "imageFile" dans App\Entity\Media\Avatar
        if ($avatar && $this->helper) {
            // NB: on doit passer le **nom du champ** annoté (imageFile), pas le nom de mapping ('avatar')
            $this->session()->set('avatar', $this->helper->asset($avatar, 'imageFile'));
        }

        // Board via la relation Wbcustomers → Board (si présente)
        $wb = \method_exists($customer, 'getBoardwbcustomer') ? $customer->getBoardwbcustomer() : null;
        $board = $wb && \method_exists($wb, 'getBoard') ? $wb->getBoard() : null;

        if ($board instanceof Board) {
            $this->currentBoard = $board;
            $this->board        = $board;
        }
    }
}
