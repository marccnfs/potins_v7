<?php


namespace App\Service\Member;


use App\AffiEvents;
use App\Entity\Customer\Customers;
use App\Entity\Member\Activmember;
use App\Entity\Boards\Board;
use App\Entity\Member\Boardslist;
use App\Event\CustomerEvent;
use App\Repository\GpsRepository;
use App\Service\Localisation\LocalisationServices;
use App\Service\Registration\Sessioninit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class MemberFactor
{
    private EntityManagerInterface $em;
    private Sessioninit $sessionInit;
    private GpsRepository $gpsRepository;
    private EventDispatcherInterface $eventDispatcher;
    private LocalisationServices $localise;



    public function __construct( EntityManagerInterface $em,
                                 Sessioninit $sessionInit,GpsRepository $gpsRepository,
                                EventDispatcherInterface $eventDispatcher, LocalisationServices $localise){

        $this->em = $em;
        $this->sessionInit = $sessionInit;
        $this->eventDispatcher = $eventDispatcher;
        $this->localise = $localise;
        $this->gpsRepository = $gpsRepository;
    }


    public function NewMember(Customers $objcustomer): Activmember
    {
        $member=new Activmember();
        $member->setPermission([0,0,0]); // creation autonome
        $member->setCustomer($objcustomer);
        $objcustomer->setMember($member);
        $member->setName($objcustomer->getProfil()->getFirstname()??$objcustomer->getProfil()->getEmailfirst());
        $this->em->persist($member);
        $this->em->flush();
        return $member;
    }

    /*----------en cours -------------------------*/

    public function addwebsitedispatch(Activmember $dispatch, Board $board): Activmember
    {

        $spwsite=New Boardslist();
        $spwsite->setIsadmin(true);
        $spwsite->setBoard($board);
        $spwsite->setRole('superadmin');
        $board->addBoardslist($spwsite);
        $dispatch->addBoardslist($spwsite);
        $this->em->persist($board);
        $this->em->persist($dispatch);
        $this->em->flush();
        $event = new CustomerEvent($dispatch->getCustomer(),$board);
        $this->eventDispatcher->dispatch($event, AffiEvents::DISPATCH_INVIT_WEBSITE);
        return $dispatch;
    }





    public function NewDispatchByWebsite(Customers $objcustomer, $website): Activmember
    {
        $dispatch=new Activmember();
        $dispatch->setPermission([0,0,0]); // creation autonome
        $dispatch->setCustomer($objcustomer);
        $objcustomer->setMember($dispatch);
        $dispatch->setName($objcustomer->getProfil()->getFirstname()??$objcustomer->getProfil()->getEmailfirst());
        $dispatch->setLocality($website->getLocality());
        $this->em->persist($dispatch);
        $this->em->flush();
        $this->sessionInit->preInitSpaceWeb($dispatch);
        $this->sessionInit->chenageLoc($dispatch);
        return $dispatch;
    }


    public function confirmDispatch($dispatch,$coord,): Activmember
    {
        $gps=$this->localise->defineCity($coord['lat'], $coord['lon']);
        if(!$gps){
            $gps = $this->gpsRepository->find(3); //par defaut bouaye
        }
        $dispatch->setLocality($gps);
        $this->em->persist($dispatch);
        $this->em->flush();
        $this->sessionInit->preInitSpaceWeb($dispatch);
        $this->sessionInit->chenageLoc($dispatch);
        return $dispatch;
    }


    public function confirmLocByWebsite($dispatch,$website): Activmember
    {
        $dispatch->setLocality($website->getLocality());
        $this->em->persist($dispatch);
        $this->em->flush();
        $this->sessionInit->preInitSpaceWeb($dispatch);
        $this->sessionInit->chenageLoc($dispatch);
        return $dispatch;
    }


    public function majDistach(Activmember $dispatch, $form): Activmember
    {
        $dispatch->setName($form['namespaceweb']->getData());
        $idgps=$form['idlocate']->getData();
        if($idgps){
            $testgps=explode(" ",$idgps);
            $gps = $this->localise->changeLocate(null, $testgps[0], $testgps[1])??$this->gpsRepository->find(3);
        }else {
            $gps = $this->gpsRepository->find(3); //par defaut bouaye
        }
        $dispatch->setLocality($gps);
        $this->em->persist($dispatch);
        $this->em->flush();
        $this->sessionInit->chenageLoc($dispatch);
        return $dispatch;
    }

    /**
     * creation d'un dispatch depuis une requete via formulaire de conversation du website (non user)
     */
    public function newDispatchclient(Customers $objcustomer, $tabmenber): Activmember
    {
        $dispatch=new Activmember();
        $spwsite=New Boardslist();
        $spwsite->setIsadmin(false);
        $spwsite->setBoard($tabmenber['website']);
        $spwsite->setRole("client");
        $spwsite->setTermes([0]);
        $tabmenber['website']->addSpwsite($spwsite);
        $dispatch->addBoardslist($spwsite);
        $dispatch->setPermission([0]); //pour rejoint conversation website  --
        $dispatch->setCustomer($objcustomer);
        $objcustomer->setMember($dispatch);
        $dispatch->setName($tabmenber['name']??"auto".$objcustomer->getNumclient()->getNumero());
        $this->em->persist($tabmenber['website']);
        $this->em->persist($dispatch);
        $this->em->persist($spwsite);
        $this->em->flush();
        return $dispatch;
    }

    /**
     * creation d'un dispatch depuis une requete via formulaire de conversation du website (non user)
     */
    public function newDispatchmember(Customers $objcustomer, $tabmenber): Activmember
    {
        $dispatch=new Activmember();
        $spwsite=New Boardslist();
        $spwsite->setIsadmin(true);
        $spwsite->setBoard($tabmenber['website']);
        $spwsite->setRole("member");
        $spwsite->setTermes([0]);
        $tabmenber['website']->addSpwsite($spwsite);
        $dispatch->addBoardslist($spwsite);
        $dispatch->setPermission([0,0,1]); //invitation et creation d'un membre  -- //todo atte,top, vient en contradiction avec la vérif charte par localisation
        $dispatch->setCustomer($objcustomer);
        $objcustomer->setMember($dispatch);
        $dispatch->setName($tabmenber['email']);
        $this->em->persist($tabmenber['website']);
        $this->em->persist($dispatch);
        $this->em->flush();
        return $dispatch;
    }


    public function newDispatchAdmin(Customers $objcustomer, $tabmenber): Activmember
    {
        $dispatch=new Activmember();
        $spwsite=New Boardslist();
        $spwsite->setIsadmin(true);
        $spwsite->setBoard($tabmenber['website']);
        $spwsite->setRole("superadmin");
        $spwsite->setTermes([0]);
        $tabmenber['website']->addSpwsite($spwsite);
        $dispatch->addBoardslist($spwsite);
        $dispatch->setPermission([0,0,1]);
        $dispatch->setCustomer($objcustomer);
        $objcustomer->setMember($dispatch);
        $dispatch->setName($tabmenber['mail']);
        $this->em->persist($tabmenber['website']);
        $this->em->persist($dispatch);
        $this->em->flush();
        return $dispatch;
    }
}