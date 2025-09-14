<?php


namespace App\Notification;


use App\AffiEvents;
use App\Entity\DispatchSpace\DispatchSpaceWeb;
use App\Entity\DispatchSpace\Spwsite;
use App\Entity\LogMessages\Loginner;
use App\Entity\LogMessages\Msgs;
use App\Entity\LogMessages\Tbmsgs;
use App\Entity\Users\Contacts;
use App\Entity\Users\ProfilUser;
use App\Entity\Websites\Website;
use App\Event\MessageEvent;
use App\Repository\Entity\ContactationRepository;
use App\Repository\Entity\DispatchSpaceWebRepository;
use App\Repository\Entity\MsgsRepository;
use App\Repository\Entity\MsgWebisteRepository;
use App\Repository\Entity\TbmsgsRepository;
use App\Service\Modules\Mailator;
use App\Util\Canonicalizer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Notificator
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Canonicalizer
     */
    private $emailCanonicalizer;

    /**
     * @var MsgsRepository
     */
    private $mesrepo;
    /**
     * @var DispatchSpaceWebRepository
     */
    private $repodispatch;
    /**
     * @var ContactationRepository
     */
    private $repoContact;
    /**
     * @var false
     */
    private $member;
    /**
     * @var false
     */
    private $contact;
    /**
     * @var DispatchSpaceWeb|null
     */
    private $expe;
    private $session;
    private EventDispatcherInterface $eventdispatcher;


    public function __construct(EventDispatcherInterface $eventDispatcher,SessionInterface $session, DispatchSpaceWebRepository $dispatchSpaceWebRepository, ContactationRepository $contactationRepository,MsgWebisteRepository $msgWebisteRepository, EntityManagerInterface $entityManager, TbmsgsRepository $tbmsgsRepository, Canonicalizer $emailCanonicalizer, Mailator $mailator)
    {
        $this->msgWebisteRepository=$msgWebisteRepository;
        $this->entityManager = $entityManager;
        $this->tabreadrepo=$tbmsgsRepository;
        $this->emailCanonicalizer = $emailCanonicalizer;
        $this->mailator=$mailator;
        $this->repodispatch=$dispatchSpaceWebRepository;
        $this->repoContact=$contactationRepository;
        $this->session=$session;
        $this->eventdispatcher=$eventDispatcher;
    }





}