<?php


namespace App\Notification;


use App\Entity\DispatchSpace\DispatchSpaceWeb;
use App\Entity\DispatchSpace\Spwsite;
use App\Entity\LogMessages\Msgs;
use App\Entity\LogMessages\PrivateConvers;
use App\Entity\LogMessages\Tbmsgs;
use App\Entity\Marketplace\Offres;
use App\Entity\Customer\Transactions;
use App\Entity\Users\Contacts;
use App\Entity\Users\ProfilUser;
use App\Entity\Websites\Website;
use App\Repository\Entity\DispatchSpaceWebRepository;
use App\Repository\Entity\MsgsRepository;
use App\Repository\Entity\MsgWebisteRepository;
use App\Repository\Entity\PrivateConversRepository;
use App\Repository\Entity\TbmsgsRepository;
use App\Service\Modules\Mailator;
use App\Util\Canonicalizer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;

class NotificationMessageor
{
    /**
     * @var MsgWebisteRepository
     */
    private $msgWebisteRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var TbmsgsRepository
     */
    private $tabreadrepo;
    /**
     * @var Canonicalizer
     */
    private $emailCanonicalizer;
    /**
     * @var Mailator
     */
    private $mailator;
    /**
     * @var MsgsRepository
     */
    private $mesrepo;
    /**
     * @var DispatchSpaceWebRepository
     */
    private DispatchSpaceWebRepository $dispatchSpaceWebRepository;
    /**
     * @var PrivateConversRepository
     */
    private PrivateConversRepository $msgPrivateRepository;


    public function __construct( DispatchSpaceWebRepository $dispatchSpaceWebRepository, PrivateConversRepository $privateConversRepository, EntityManagerInterface $entityManager, TbmsgsRepository $tbmsgsRepository, Canonicalizer $emailCanonicalizer, Mailator $mailator)
    {
        $this->msgPrivateRepository=$privateConversRepository;
        $this->entityManager = $entityManager;
        $this->tabreadrepo=$tbmsgsRepository;
        $this->emailCanonicalizer = $emailCanonicalizer;
        $this->mailator=$mailator;
        $this->dispatchSpaceWebRepository = $dispatchSpaceWebRepository;
    }


    /**
     * @param $id
     * @return mixed
     */
    public function messnoreadToList($id){
            return $this->tabreadrepo->findPrivateMessnoreadToList($id);
    }

    /**
     * @param $pagination
     * @param $dispatch DispatchSpaceWeb
     * @return array
     */
    public function sortmessage($pagination, DispatchSpaceWeb $dispatch): array
    {
        //$sort=get_object_vars($paginator);
        // $msgs=array_reverse((array)$pagination->getItems()); // ???

        $msgs=$pagination->getItems();
        $stop=false;
        foreach ($msgs as $key => $msg) { // on marque les message déjà lu ou dont est l'auteur
            $isread=true;
            foreach ($msg->getMsgs() as $m) {  //pour chaque messagges
                if ($m->getAuthor() !== $dispatch) {  // si pas authir du m
                    foreach ($m->getTabreaders() as $read) {
                        if ($read->getDispatch() === $dispatch && !$read->getIsRead()) {
                            $isread = false;
                        }
                        if(!$isread) break;
                    }
                }
                if(!$isread) break;
            }
            //  $msg->setSender($isread);

        }
        return $msgs;
    }


    /**
     * @param $offre Offres
     * @param $msg Msgs
     * @param $client
     * @return void
     * @throws Exception
     */
    public function addTabExpeAndDest($offre, $msg, $client){

            $tabexpe = new Tbmsgs();
            $tabexpe->setDispatch($client);
            $tabexpe->setIsRead(true);
            $tabexpe->setReadAt(new dateTime());
            $msg->addTabreader($tabexpe);
            $tabDest = new Tbmsgs();
            $tabDest->setDispatch($client);
            $tabDest->setIsRead(true);
            $tabDest->setReadAt(new dateTime());
            $msg->addTabreader($tabexpe);

        return;
    }

    /**
     * @param $exp
     * @param $msg Msgs
     * @param $dest
     * @return void
     * @throws Exception
     */
    public function addTabPrivateExpeAndDest($exp, Msgs $msg, $dest){

        $tabexpe = new Tbmsgs();
        $tabexpe->setDispatch($exp);
        $tabexpe->setIsRead(true);
        $tabexpe->setReadAt(new dateTime());
        $msg->addTabreader($tabexpe);
        $tabDest = new Tbmsgs();
        $tabDest->setDispatch($dest);
        $tabDest->setIsRead(true);
        $tabDest->setReadAt(new dateTime());
        $msg->addTabreader($tabexpe);

    }

    /**
     * @param $exp
     * @param $msg Msgs
     * @param $dest
     * @return void
     * @throws Exception
     */
    public function addTabPrivateExpeAndDestWebsite($exp, Msgs $msg, $dest){

        $tabexpe = new Tbmsgs();
        $tabexpe->setDispatch($exp);
        $tabexpe->setIsRead(true);
        $tabexpe->setReadAt(new dateTime());
        $msg->addTabreader($tabexpe);
        $tabDest = new Tbmsgs();
        $tabDest->setDispatch($dest);
        $tabDest->setIsRead(true);
        $tabDest->setReadAt(new dateTime());
        $msg->addTabreader($tabexpe);

    }



    /**
     * @param $website Website
     * @param $msg
     * @param $client DispatchSpaceWeb
     * @throws Exception
     */
    public function addTabaReponseClientAtAdmin($website, $msg, $client){  //reponse d'un cleint vers website
        /** @var DispatchSpaceWeb $dpt */
        foreach ($this->tabSpw($website) as $dpt) {

            if($dpt['admin']){
                $tab = new Tbmsgs();
                $tab->setDispatch($dpt['reader']);
                $msg->addTabreader($tab);
            }else{
                if($dpt['reader']==$client){
                    $tab = new Tbmsgs();
                    $tab->setDispatch($dpt['reader']);
                    $tab->setIsRead(true);
                    $tab->setReadAt(new dateTime());
                    $msg->addTabreader($tab);
                }
            }
        }
    }


    /**
     * @param $para
     * @param $isuser
     * @throws Exception
     */
    public function newOptionMarket($para, $isuser){
        $convers = $para['convers'];
        $website = $para['website'];
        $client = $para['client'];
        /** @var Offres $offre */
        $offre = $para['offre'];
        $contentmsg = $para['form']->get('content')->getData();

        $convers->setWebsitedest($website);
        $convers->addDispatchdest($offre->getAuthor());
        $convers->setDispatchopen($client);
        $convers->setSubject('option achat sur article N°');  //todo


        $transac = new Transactions();
        $transac->setConvers($convers);
        $transac->setOffre($offre);
        $transac->setClient($client);
        $date=new DateTime();
        $transac->setEndAt($date->modify('+1 month'));  // todo a peaufiner
        $offre->addTransaction($transac);

        $msg= new Msgs();
        $msg->setBodyTxt($contentmsg);
        $msg->setContentHtml("");
        $msg->setAuthor($client);
        $this->addTabExpeAndDest($offre, $msg, $client);

        $convers->addMsg($msg);
        $this->entityManager->persist($transac);
        $this->entityManager->persist($offre);
        $this->entityManager->persist($convers);
        $this->entityManager->flush();

        $this->mailator->notifiNewConversDestAndExpe($offre, $client, $convers);
        return;
    }

    /**
     * @param $para
     * @param $isuser
     * @throws Exception
     */
    public function newPrivateConvers($para){
        $convers = $para['message'];
        $expe = $para['dispatch'];
        $contentmsg = $para['form']->get('content')->getData();
        $dest=$para['form']->get('destinataire')->getData();

        $convers->setWebsitedest("");
        $convers->addDispatchdest($dest);
        $convers->setDispatchopen($expe);
        $convers->setSubject('msg private');  //todo

        $msg= new Msgs();
        $msg->setBodyTxt($contentmsg);
        $msg->setContentHtml("");
        $msg->setAuthor($expe);
        $this->addTabPrivateExpeAndDest($expe, $msg, $dest);

        $convers->addMsg($msg);
        $this->entityManager->persist($convers);
        $this->entityManager->flush();

     //   $this->mailator->notifiNewPrivateConversDestAndExpe($expe, $dest, $convers);  todo reste le sender a faire mais voir si necessaire la notification ??
    }

    public function newPrivateConversdispatch($form,$privatemessage,$dispatch,$contact){

        $contentmsg = $form['content']->getData();
        $privatemessage->setWebsitedest(null);
        $privatemessage->setSubject('msg private');  //todo

        $msg= new Msgs();
        $msg->setBodyTxt($contentmsg);
        $msg->setContentHtml("");
        $msg->setAuthor($dispatch);
        $this->addTabPrivateExpeAndDest($dispatch, $msg, $contact);

        $privatemessage->addMsg($msg);
        $this->entityManager->persist($privatemessage);
        $this->entityManager->flush();

        //   $this->mailator->notifiNewPrivateConversDestAndExpe($expe, $dest, $convers);  todo reste le sender a faire mais voir si necessaire la notification ??
    }

    /**
     * @param $convers PrivateConvers
     * @param $dispatch DispatchSpaceWeb
     * @param $content
     * @return Msgs
     * @throws Exception
     */
    public function addConvers(PrivateConvers $convers, DispatchSpaceWeb $dispatch, $content){
        $msg = new Msgs();
        $msg->setBodyTxt($content);
        $msg->setContentHtml("");
        $msg->setConversprivate($convers);
        $msg->setAuthor($dispatch);

        $this->addTabPrivateExpeAndDest($dispatch, $msg, $convers->getDispatchdest()[0]);

        $convers->addMsg($msg);
        $this->entityManager->persist($convers);
        $this->entityManager->flush();
        return $msg;
    }




    //------------------------------ non controlés -------------------------------------//


    /**
     * @param $website Website
     * @param $msg
     * @param $client
     * @param $dispatch
     * @throws Exception
     */
    public function addTabaReponseAdminAtClient($website, $msg, $client, $dispatch){   //reponse  d'un admin vers un client
        /** @var DispatchSpaceWeb $dpt */
        foreach ($this->tabSpw($website) as $dpt) {
            if($dpt['admin']){
                $tab = new Tbmsgs();
                $tab->setDispatch($dpt['reader']);
                if ($dpt['reader'] == $dispatch) {
                    $tab->setDispatch($dpt['reader']);
                    $tab->setIsRead(true);
                    $tab->setReadAt(new dateTime());
                }
                $msg->addTabreader($tab);
            }else{
                if($dpt['reader']==$client){
                    $tab = new Tbmsgs();
                    $tab->setDispatch($dpt['reader']);
                    $msg->addTabreader($tab);
                }
            }
        }
    }


    /**
     * @param $website Website
     * @return array
     */
    public function tabSpw($website){  //tab de tous les dispatch d'un website
        $tabreaders=[];
        $i=0;
        /** @var Spwsite $reader */
        foreach ($website->getSpwsites() as $reader) {
            $tabreaders[$i]['reader'] = $reader->getDisptachwebsite();
            $tabreaders[$i]['admin'] = $reader->getIsadmin();
            $tabreaders[$i]['role'] = $reader->getRole();
            ++$i;
        }
        return $tabreaders;
    }

    /**
     * @param $tab
     * @param $dispatch
     * @return array
     */
    public function tabSpwWhithOutDispatch($tab,$dispatch){  // tab de tous  les admin d'un website sauf l'utilisateur en cours
        $tabdest=[];
        /** @var Spwsite $reader */
        foreach ($tab as $reader) {
            if($reader['reader']!==$dispatch){
                if($reader['role']!=='client') $tabdest[] = $reader['reader']->getCustomer()->getEmailcontact();
            }
        }
        return $tabdest;
    }

    /**
     * tab de tous les admin et d'un client d'un website sauf l'utilisateur en cours - pour reponse conversation exp :client Ne sert à rien ???
     *
     * @param $website Website
     * @param $dispatch DispatchSpaceWeb
     * @return bool
     */
    public function isTheClient($website, $dispatch){
        /** @var Spwsite $reader */
        foreach ($website->getSpwsites() as $reader) {
            if($reader->getRole()=='client'){
                if($reader->getDisptachwebsite() === $dispatch) return true;
            }
        }
        return false;
    }

    /**
     * @param $website
     * @param $dispatch
     * @return array
     * @throws Exception
     */
    public function addTabAll($website, $dispatch, $msg){  // ajout uniquement les membres et admin  retroun le array des dsinatire des notifs hors auteur
        $tabdest=[];
        foreach ($this->tabSpw($website) as $dpt) {
            if($dpt['admin'] || $dpt['role']=="member") {
                $tab = new Tbmsgs();
                $tab->setDispatch($dpt['reader']);
                if ($dpt['reader'] === $dispatch) {
                    $tab->setIsRead(true);
                    $tab->setReadAt(new dateTime());
                }else{
                    $tabdest[]=$dpt['reader']->getCustomer()->getEmailcontact();
                }
                $msg->addTabreader($tab);
            }
        }
        return  $tabdest;
    }

    /**
     * @param $website
     * @param $msg
     */
    public function addTabadmin($website,  $msg){
        /** @var DispatchSpaceWeb $dpt */
        foreach ($this->tabSpw($website) as $dpt) {
            if($dpt['admin']){
                $tab = new Tbmsgs();
                $tab->setDispatch($dpt['reader']);
                $msg->addTabreader($tab);
            }
        }
    }

    /**
     * @param $d
     * @return bool|void
     * @throws Exception
     */
    public function newMessage($d)
    {

        $message = $d['messagewb'];
        $website = $d['website'];
        $dispatch = $d['dispatch'];
        $contact = $d['contact'];
        $contentmsg = $d['form']->get('content')->getData();

        // 1- identification de l'expediteur
        if ($dispatch) {
            $member = true;
            $message->setSpacewebexpe($dispatch);
            $message->setIsspaceweb(true);
            $message->setIsclient($this->isTheClient($website, $dispatch));
            $expediteur = $dispatch;
        }else{
            $member = false;
            if ($contact) {
                $identity = $contact->getUseridentity();
                $contact->setDatemajAt(new \DateTime());
            } else {
                $contact = new Contacts();
                $identity = new profilUser();
                // todo recuperation des info source du contact et validation des data formulaire
                $contact->setValidatetop(true);
                if (!$contact->getValidatetop()) return false; // todo  validation anti robot
                $identity->setFirstname($d['form']->get('username')->getData());
                $identity->setLastname("");
                $email = $d['form']->get('email')->getData();
                $identity->setEmailfirst($email);
                $contact->setEmailCanonical($this->emailCanonicalizer->canonicalize($email)); //todo voir si necessaire de garder
                $contact->setUseridentity($identity);
                $this->entityManager->persist($contact);
                $this->entityManager->flush();
            }
            $expediteur = $identity;
            $message->setContactexp($contact);
            $message->setIsspaceweb(false);
        }

        // 2 - hydratation de l'objet messagesubject
        //$message->setSubject($d['form']->get('subject')->getData());
        $message->setSubject((substr($contentmsg, 0, 25)) . '...');
        $message->setWebsitedest($website);
        $website->addMsg($message);

        $this->entityManager->persist($website);
        $this->entityManager->flush();

        $msg= new Msgs();
        $msg->setBodyTxt($contentmsg);
        $msg->setContentHtml(""); // todo voir html et txt
        $tab=[];
        if($dispatch){
            $msg->setAuthor($dispatch); // todo verfi que ce test dans le readconvers ne gene pas ???
            if(!$message->getIsclient()){
                $tab=$this->addTabAll($website, $dispatch, $msg);
            }else {
                $tab=$this->addTabadminAndClient($website, $msg, $dispatch); //ajout des tbsmg (admin et le client) - retroun le tab des destinatires (notif)
            }
        }else{
            $this->addTabadmin($website, $msg);
        }
        $message->addMsg($msg);
        $this->entityManager->persist($message);
        $this->entityManager->flush();
        if($dispatch && !$message->getIsclient()) {  //auteur membre pas client
            $this->mailator->notifMessageMemberwebsite($website, $tab, $message); //true =>resa, false =>contact
        }else{
            $this->mailator->newMail($website, $expediteur, $member, $message);   //envoie message uniquement au email du website (template)
        }
        return;
    }


    /**
     * @param $id
     * @param $pw Spwsite
     * @return array
     */
    public function messwebsite($id, $pw){
        // recuperation des message et tri chrono (requete sur tous les messages du website et unset de ceux qui ne concernent par le dispatch)
       $sort= $this->msgWebisteRepository->findMsgsWebsite($id);
        $msgs=array_reverse((array)$sort);

        $stop=false;
        foreach ($msgs as $key => $msg) { // on marque les message déjà lu ou dont est l'auteur
            $isread=true;
            foreach ($msg->getMsgs() as $m) {  //pour chaque messagges
                if ($m->getAuthor() !== $pw->getDisptachwebsite()) {  // si pas authir du m
                    foreach ($m->getTabreaders() as $read) {
                        if ($read->getDispatch() === $pw->getDisptachwebsite() && !$read->getIsRead()) {
                            $isread = false;
                        }
                        if(!$isread) break;
                    }
                }
                if(!$isread) break;
            }
            $msg->setSender($isread);

            if ($pw->getRole()==='client') {  // dans le cas où le pw est un cleint ou un membre //todo pourquoi aussi les membres ?
                if (!$msg->getSpacewebexpe() || $msg->getSpacewebexpe()->getId() !== $pw->getDisptachwebsite()->getId()) { // si message d'un contact ou autre que l'utilisateur
                    unset($msgs[$key]);
                }
            }

            if ($pw->getRole()==='member') {  // dans le cas où le pw est un cleint ou un membre //todo pourquoi aussi les membres ?
                if (!$msg->getSpacewebexpe() || $msg->getCreateAt()<$pw->getCreateAt()) { // si message d'un contact ou pw creer après le message
                    unset($msgs[$key]);
                }
            }

            if ($pw->getRole()==='admin') {  // dans le cas où le pw est un cleint ou un membre //todo pourquoi aussi les membres ?
                if ($msg->getCreateAt() < $pw->getCreateAt()) { // si message d'un contact ou pw creer après le message
                    unset($msgs[$key]);
                }
            }
        }
        return $msgs;
    }


    /**
     * @param $tab
     * @param DispatchSpaceWeb $pw Spwsite*
     */
    public function readersconvers($tab, DispatchSpaceWeb $pw)
    {
        /** @var Msgs $m */
        foreach ($tab as $m) {
            $diptch = false;
            foreach ($m->getTabreaders() as $read) {
                /** @var Tbmsgs $read */
                if ($read->getDispatch() === $pw->getDisptachwebsite()) {
                    $diptch = true;
                    if (!$read->getIsRead()) { //si read du dipsatch n'est pas lu
                        $read->setIsRead(true);
                        $read->setReadAt(new DateTime());
                        $this->entityManager->persist($read);
                        $this->entityManager->flush();
                    }
                }
                if($diptch) break;
            }
            if(!$diptch){    // rajout fonction si un nouveau membre prend connaissance d'ancien message
                $ntb=new Tbmsgs();
                $ntb->setDispatch($pw->getDisptachwebsite());
                $ntb->setIsRead(true);
                $ntb->setReadAt(new DateTime());
                $m->addTabreader($ntb);
                $this->entityManager->persist($m);
                $this->entityManager->flush();
            }
        }
        return ;
    }

    /**
     * @param $pw Spwsite
     * @return bool
     */
    public function sortMessgsPw($msg, $pw){
        if (!$pw->isAdmin() && $pw->getRole()==='contact') {  // dans le cas où le pw est un contact il faut qu'il soit le createur du message (spacewebexpe)
            if (!$msg->getSpacewebexpe() || $msg->getSpacewebexpe()->getId() !== $pw->getDisptachwebsite()->getId()) {
                return false;
            }else{
                return true;
            }
        }
        return true;
    }




    /**
     * @param $website Website
     * @param $pw Spwsite
     * @return mixed
     */
    public function messnoread($website, $pw){
        $reads= $this->tabreadrepo->findnoread($website->getId());
        /** @var Tbmsgs $read */
        foreach ($reads as $key => $read){
            if ($read->getDispatch()->getId() !== $pw->getDisptachwebsite()->getId()) {
                unset($reads[$key]);
            }
        }
        return $reads;
    }


}