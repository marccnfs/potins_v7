<?php


namespace App\Service\SpaceWeb;


use App\AffiEvents;
use App\Entity\Admin\Wbcustomers;
use App\Entity\Customer\Customers;
use App\Entity\Member\Activmember;
use App\Entity\Member\Boardslist;
use App\Entity\Member\TabDotWb;
use App\Entity\Media\Background;
use App\Entity\Media\Pict;
use App\Entity\Sector\Adresses;
use App\Entity\Sector\Sectors;
use App\Entity\UserMap\Taguery;
use App\Entity\Boards\Template;
use App\Entity\Boards\Board;
use App\Event\CustomerEvent;
use App\Event\InvitMailEvent;
use App\Lib\MsgAjax;
use App\Lib\Tools;
use App\Repository\GpsRepository;
use App\Repository\TabDotWbRepository;
use App\Repository\TagueryRepository;
use App\Service\Gestion\Numerator;
use App\Service\Localisation\LocalisationServices;
use App\Service\Media\Uploadator;
use App\Module\Modulator;
use App\Service\Registration\CreatorUser;
use App\Service\Registration\Sessioninit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class BoardlistFactor
{
    private EntityManagerInterface $em;
    private Uploadator $uploadator;
    private Sessioninit $sessioninit;
    private TagueryRepository $tagueryRepository;
    private GpsRepository $gpsRepository;
    private Numerator $numerator;
    private CreatorUser $creator;
    private EventDispatcherInterface $eventDispatcher;
    private LocalisationServices $localise;
    private Modulator $modulator;
    private TabDotWbRepository $tabdotwbrepo;


    public function __construct(TagueryRepository $tagueryRepository, EntityManagerInterface $em,CreatorUser $creator,
                                Uploadator $uploadator, Sessioninit $sessioninit,Numerator $numerator,GpsRepository $gpsRepository,
                                EventDispatcherInterface $eventDispatcher, LocalisationServices $localise, Modulator $modulator,TabDotWbRepository $tabDotWbRepository){

        $this->em = $em;
        $this->uploadator = $uploadator;
        $this->sessioninit = $sessioninit;
        $this->tagueryRepository = $tagueryRepository;
        $this->numerator = $numerator;
        $this->creator = $creator;
        $this->eventDispatcher = $eventDispatcher;
        $this->localise = $localise;
        $this->gpsRepository = $gpsRepository;
        $this->modulator=$modulator;
        $this->tabdotwbrepo=$tabDotWbRepository;
    }

    public function initTemplate(Board $board, $form): Board
    {
        $template=$board->getTemplate();
        $file=$form['template']['logotemplate']->getData();
        if($file!==null){ //todo remove ancien logo si changement
            $pict=new Pict();
            $this->uploadator->Upload($file, $pict);
            $template->setLogo($pict);
        }

        $filefond=$form['template']['background']->getData();
        if($filefond!==null){ //todo remove ancien logo si changement
            $background=new Background();
            $this->uploadator->Upload($filefond, $background);
            $template->setBackground($background);
        }
        $tagueirelist=$template->getTagueries();
        foreach ($tagueirelist as $sup){
            $template->removeTaguery($sup);
        }
        $tags=Tools::cleanTags($form['template']['tagueries']->getData());
        foreach ($tags as $tag){
            if(!$resulttag=$this->tagueryRepository->findOneBy([ 'name'=>$tag])){
                $resulttag=New Taguery();
                $resulttag->setName($tag);
                $resulttag->setPhylo($board->getSlug());
            }
            $template->addTaguery($resulttag);
        }
        return $board;
    }

    public function createFirstBoard(Customers $customer,Activmember $member, Boardslist $boardslist,$form): Board
    {

        $nums=$this->numerator->getActiveNumeratewebsite();
       /*
        $tabdot=$this->tabdotwbrepo->findOneBy(['email'=>$customer->getEmailcontact()]);
        if($tabdot){
            $website=$tabdot->getBoard();
            $website->setAttached(true);
            $this->em->remove($tabdot);
            $this->em->flush();
        }else{
        */
        $board=new Board();
        $board->setNameboard($form['namewebsite']->getData());
        $key=substr($board->getNameboard(),5).bin2hex(random_bytes(16));
        $board->setCodesite($key);
        $template=new Template();
        $template->setEmailspaceweb($customer->getEmailcontact());
        $board->setTemplate($template);
        $sector=new Sectors();
        $sector->setCodesite($board->getCodesite());
        $cli=new Wbcustomers();
        $cli->setBoard($board);
        $board->setWbcustomer($cli);
        $cli->setNumero($nums->getNumClient());
        $cli->setOrdre(date('Y'));
        $boardslist->setMember($member);
        $boardslist->setBoard($board);
        $boardslist->setRole('member');
        $member->addBoardslist($boardslist);
        $boardslist->setIsdefault(true);
        $boardslist->activeAdmin();
        $this->em->persist($boardslist);
        $this->em->persist($member);
        $this->em->persist($board);
        $this->em->flush();
        $this->sessioninit->InitMember($member);
        return $board;
    }

    public function createMediaBoard(Customers $customer,Activmember $member,Boardslist $boardslist, $data): Board
    {
        $board=new Board();
        $cli=new Wbcustomers();
        $template=new Template();
        $sector=new Sectors();
        $adress = new Adresses();
        $board->setLocatemedia(true);
        $board->setNameboard($data['titre']);
        $key=substr($board->getNameboard(),5).bin2hex(random_bytes(16));
        $board->setCodesite($key);
        $template->setEmailspaceweb($customer->getEmailcontact());
        $board->setTemplate($template);
        $action=$this->localise->adressor($adress,json_decode((string) $data['adresse'], true));
        $sector->addAdresse($action['adress']);
        $sector->setCodesite($board->getCodesite());
        $board->setLocality($action['gps']);
        $member->setLocality($action['gps']);
        $member->setSector($sector);
        $member->setPermission([2,1,1]);
        $nums=$this->numerator->getActiveNumeratewebsite();
        $cli->setBoard($board);
        $cli->setNumero($nums->getNumClient());
        $cli->setOrdre(date('Y'));
        $board->setWbcustomer($cli);
        $boardslist->setMember($member);
        $boardslist->setBoard($board);
        $boardslist->setRole('media');
        $member->addBoardslist($boardslist);
        $boardslist->setIsdefault(true);
        $boardslist->activeAdmin();
        $this->em->persist($boardslist);
        $this->em->persist($member);
        $this->em->persist($board);
        $this->em->flush();
       // $this->sessioninit->InitMember($member);
        return $board;
    }

    public function addWebsite(Activmember $member, Boardslist $bdl, Board $board): Board
    {
        $bdl->setMember($member);
        $bdl->setBoard($board);
        $bdl->setRole('admin');
        $bdl->setIsadmin(false);
        $board->addBoardslist($bdl);
        $member->addBoardslist($bdl);
        //$spw->activeAdmin(); fonction pour donner les droit admin
        $this->em->persist($bdl);
        $this->em->persist($member);
        $this->em->persist($board);
        $this->em->flush();
        return $board;
    }


    public function addwebsiteclient($tabmember): Activmember
    {  //contact (ou null),type, website, mail, pass, name,
        $customer=$this->creator->createUserByConversToJoinWebsite($tabmember);
        return $this->newDispatchclient($customer,$tabmember);
    }


    public function addWebsiteNewMember($tabmember): Activmember
    {  //contact (ou null),type, website, mail, pass, name,
        $customer=$this->creator->createUserByMailToInvitWebsite($tabmember);
        return  $this->newDispatchmember($customer,$tabmember);
    }

    /**
     * invitation-ajout d'un admin à un website par l'admin
     */
    public function addWebsiteNewadmin($tabmember): Activmember
    {  //contact (ou null),type, website, mail, pass, name,
        $customer=$this->creator->createUserByMailToInvitWebsite($tabmember);
        return  $this->newDispatchAdmin($customer,$tabmember);
    }

    /**
     * invitation-ajout d'un admin à un website par l'admin
     * @param $mail
     * @param $website
     * @return TabDotWb
     */
    public function invitMailToAdmin($mail, $website): TabDotWb
    {
        $tabdot=new TabDotWb();
        $tabdot->setWebsite($website);
        $tabdot->setEmail($mail);
        $this->em->persist($tabdot);
        $this->em->flush();
        $event= new InvitMailEvent($mail, $website);
        $this->eventDispatcher->dispatch($event, AffiEvents::INVIT_TOADMIN_BYMAIL);
        return  $tabdot;
    }


    public function addwebsitedispatch(Activmember $dispatch, Board $website): Activmember
    {  //$mail, $website, $password, $name

        $spwsite=New Boardslist();
        $spwsite->setIsadmin(true);
        $spwsite->setBoard($website);
        $spwsite->setRole('superadmin');
        $website->addBoardslist($spwsite);
        $dispatch->addBoardslist($spwsite);
        $this->em->persist($website);
        $this->em->persist($dispatch);
        $this->em->flush();
        $event = new CustomerEvent($dispatch->getCustomer(),$website);
        $this->eventDispatcher->dispatch($event, AffiEvents::DISPATCH_INVIT_WEBSITE);
        return $dispatch;
    }



    public function NewDispatch(Customers $objcustomer, $coord): Activmember
    {
        $dispatch=new Activmember();
        $dispatch->setPermission([0,0,0]); // creation autonome
        $dispatch->setCustomer($objcustomer);
        $objcustomer->setMember($dispatch);
        $dispatch->setName($objcustomer->getProfil()->getFirstname()??$objcustomer->getProfil()->getEmailfirst());
        $gps=$this->localise->defineCity($coord['lat'], $coord['lon']);
        if(!$gps){ //todo revoir ça pas sur necessaire et le bon placement de la creation du gps ??
            $gps = $this->gpsRepository->find(3); //par defaut bouaye
        }
        $dispatch->setLocality($gps);
        $this->em->persist($dispatch);
        $this->em->flush();
        $this->sessioninit->preInitSpaceWeb($dispatch);
        $this->sessioninit->chenageLoc($dispatch);
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
        $this->sessioninit->preInitSpaceWeb($dispatch);
        $this->sessioninit->chenageLoc($dispatch);
        return $dispatch;
    }


    public function confirmLocByWebsite($dispatch,$website): Activmember
    {
        $dispatch->setLocality($website->getLocality());
        $this->em->persist($dispatch);
        $this->em->flush();
        $this->sessioninit->preInitSpaceWeb($dispatch);
        $this->sessioninit->chenageLoc($dispatch);
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
        $this->sessioninit->chenageLoc($dispatch);
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


    public function addSpwsite(Board $website, Activmember $dispatch): Boardslist
    {
        $spwsite=New Boardslist();
        $spwsite->setIsadmin(false);
        $spwsite->setBoard($website);
        $spwsite->setRole('member');
        $website->addBoardslist($spwsite);
        $dispatch->addBoardslist($spwsite);
        $this->em->persist($website);
        $this->em->persist($dispatch);
        $this->em->flush();
        return $spwsite;
    }


    public function addSpwsiteClient(Board $website, Activmember $dispatch): Boardslist
    {
        $spwsite=New Boardslist();
        $spwsite->setIsadmin(false);
        $spwsite->setBoard($website);
        $spwsite->setRole('client');
        $website->addBoardslist($spwsite);
        $dispatch->addBoardslist($spwsite);
        $this->em->persist($website);
        $this->em->persist($dispatch);
        $this->em->flush();
        return $spwsite;
    }


    public function createFirstWebsite(Customers $customer,Activmember $dispatchSpace): Board
    {
     // todo verifier si le website n'existe pas déjà
        $nums=$this->numerator->getActiveNumeratewebsite();
        $tabdot=$this->tabdotwbrepo->findOneBy(['email'=>$customer->getEmailcontact()]);
        if($tabdot){
            $website=$tabdot->getBoard();
            $website->setAttached(true);
            $this->em->remove($tabdot);
            $this->em->flush();
        }else{
            $website=new Board();
            $website->setNameboard($dispatchSpace->getName().'-'.$dispatchSpace->getLocality()->getSlugcity());
            $website->addLocality($dispatchSpace->getLocality());
            $template=new Template();
            $template->setEmailspaceweb($customer->getEmailcontact());
            $website->setTemplate($template);
            $sector=new Sectors();
            $template->setSector($sector);
            $cli=new Wbcustomers();
            $cli->setBoard($website);
            $website->setWbcustomer($cli);
            $cli->setNumero($nums->getNumClient());
            $cli->setOrdre(date('Y'));
        }
        $spw=new Boardslist();
        $spw->setMember($dispatchSpace);
        $spw->setBoard($website);
        $spw->setRole('superadmin');
        $dispatchSpace->addBoardslist($spw);
        $spw->activeAdmin();
        $this->modulator->initModules($customer->getServices(), $website);  // creation des modules de base avec le contactation
        $this->em->persist($spw);
        $this->em->persist($dispatchSpace);
        $this->em->persist($website);
        $this->em->flush();
        $this->sessioninit->preInitSpaceWeb($dispatchSpace);
        return $website;
    }


    public function addWebsiteLocality(Activmember $dispatch, Boardslist $spw, $form): Board
    {
        $nums=$this->numerator->getActiveNumeratewebsite();
        $website=new Board();
        $website->setNameboard($form['namewebsite']->getData());
       // $gps=$this->localise->defineCity($form['lat']->getData(), $form['lon']->getData());
       // if(!$gps) throw new Exception('erruer sur la recherche du gps');
        $website->addLocality($this->gpsRepository->find($form['idcity']->getData())??null);
        $spw->setMember($dispatch);
        $spw->setBoard($website);
        $spw->setRole('superadmin');
        $dispatch->addBoardslist($spw);
        $template=new Template();
        $template->setEmailspaceweb($dispatch->getCustomer()->getEmailcontact());
        $sector=new Sectors();
        $this->modulator->initModules($dispatch->getCustomer()->getServices(), $website);  // creation des modules de base avec le contactation
        $cli=new Wbcustomers();
        $cli->setBoard($website);
        $website->setWbcustomer($cli);
        $cli->setNumero($nums->getNumClient());
        $cli->setOrdre(date('Y'));
        $website->setTemplate($template);
        $template->setSector($sector);
        $this->em->persist($spw);
        $this->em->persist($dispatch);
        $this->em->flush();
        return $website;
    }

    public function addWebsiteLocalityAdmin(Activmember $dispatch, $form): Board
    {
        $website=new Board();
        $website->setAttached(false);
        $website->setNameboard($form['namewebsite']->getData());
        $website->addLocality($this->gpsRepository->find($form['idcity']->getData())??null);
        $template=new Template();
        $sector=new Sectors();
        $website->setTemplate($template);
        $template->setSector($sector);
        return $website;
    }

    /**
     * @param Board $website
     * @param $partner
     * @return array
     */
    public function addPartner(Board $website, $partner): array
    {
       $website->addBoardpartner($partner);
       $this->em->persist($website);
       $this->em->flush();
       return MsgAjax::MSG_SUCCESS;
    }




    /**
     * @param $website Board
     * @param $form
     * @return Board
     */


}
