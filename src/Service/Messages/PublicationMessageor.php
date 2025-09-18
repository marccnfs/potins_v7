<?php


namespace App\Service\Messages;


use App\AffiEvents;
use App\Entity\Member\Activmember;
use App\Entity\Member\Boardslist;
use App\Entity\Member\Tballmessage;
use App\Entity\LogMessages\MsgsP;
use App\Entity\LogMessages\PublicationConvers;
use App\Entity\Module\TabpublicationMsgs;
use App\Entity\Posts\Post;
use App\Entity\Users\Contacts;
use App\Entity\Users\ProfilUser;
use App\Event\MessagePublicationEvent;
use App\Lib\Tools;
use App\Repository\BoardRepository;
use App\Repository\ContactRepository;
use App\Repository\NotifmemberRepository;
use App\Repository\OffresRepository;
use App\Repository\PostRepository;
use App\Util\Canonicalizer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PublicationMessageor
{

    private EntityManagerInterface $entityManager;
    private Canonicalizer $emailCanonicalizer;
    private ContactRepository $repoContact;
    private EventDispatcherInterface $eventdispatcher;
    private OffresRepository $repoFrre;
    private PostRepository $repoPost;
    private BoardRepository $boardRepository;
    private NotifmemberRepository $notifrepo;


    public function __construct(PostRepository $postRepository, OffresRepository $offresRepository,
                                EventDispatcherInterface $eventDispatcher, ContactRepository $contactRepository,
                                EntityManagerInterface $entityManager, Canonicalizer $emailCanonicalizer,BoardRepository $boardRepository,
                                NotifmemberRepository $notifrepo)
    {
        $this->entityManager = $entityManager;
        $this->emailCanonicalizer = $emailCanonicalizer;
        $this->repoPost=$postRepository;
        $this->repoFrre=$offresRepository;
        $this->repoContact=$contactRepository;
        $this->eventdispatcher=$eventDispatcher;
        $this->boardRepository=$boardRepository;
        $this->notifrepo=$notifrepo;
    }


    /**
     * @param $data
     * @param $member
     * @return PublicationConvers
     * @throws NonUniqueResultException
     */
    public function newPublicationConvers($data, $member): PublicationConvers  //  $data = comment, slug, email, status, id, module (user, contact, nomail)
    {

        //  $content = Tools::cleanspecialcaractere($data['comment']);
        $content = Tools::cleaninput($data['comment']);
        $message = new PublicationConvers();
        $itemtallmesg=new Tballmessage();

        // 1- identification de l'expediteur
        if($member){
            $sender = $member;
            $message->setTballmsgp($itemtallmesg);
            $itemtallmesg->setMember($sender);
            $itemtallmesg->setTballmsgp($message);
            $sender->addAllmessage($itemtallmesg);
            $message->setIsmembersender(true);
        }else{
            $email = $data['email'];
            $canonicalEmail = $this->emailCanonicalizer->canonicalize($email);
            $contact = $this->repoContact->findBymail($canonicalEmail);
            if($contact instanceof Contacts){
                $sender=$contact;
                $sender->setDatemajAt(new \DateTime());
            }else{
                $sender = new Contacts();
                $identity = new profilUser();
                // todo recuperation des info source du contact et validation des data formulaire
                $sender->setValidatetop(true);
                //  if (!$this->expe->getValidatetop()) return false; // todo  validation anti robot
                $identity->setFirstname("");
                $identity->setLastname("");
                $identity->setEmailfirst($email);
                $sender->setEmailCanonical($canonicalEmail); //todo voir si necessaire de garder
                $sender->setUseridentity($identity);
                $this->entityManager->persist($sender);
                $this->entityManager->flush();
            }
            $itemtallmesg->setContact($sender);
            $sender->addAllmessage($itemtallmesg);
            $message->setTballmsgp($itemtallmesg);
            $message->setIsmembersender(false);
        }

        // 2 - intitialisation de la publication (tableau index publication)


        if($data['module']=="_blog"){
            $publication=$this->repoPost->find($data['id']); // todo faire exception pour erreur sur la requete
            if($publication->getTbmessages()== null){
                $tabmsg=new TabpublicationMsgs(); // todo en attendant que la creation soit faite sur toutes les publication par defaut à la creation
                $publication->setTbmessages($tabmsg);
                $tabmsg->setPost($publication);
                $this->entityManager->persist($publication);
            } else{
                $tabmsg=$publication->getTbmessages();
            }
        }else{
            $publication=$this->repoFrre->find($data['id']);
            if($publication->getTbmessages()== null){
                $tabmsg=new TabpublicationMsgs(); // todo en attendant que la creation soit faite sur toutes les publication par defaut à la creation
                $publication->setTbmessages($tabmsg);
                $tabmsg->setOffre($publication);
                $this->entityManager->persist($publication);
            } else{
                $tabmsg=$publication->getTbmessages();
            }
        }
/*
        if($dispatch){  //todo garde pour memoire mais supprimé - on reprend la logique ans le sortmessage  -> messageie customer ou publication du board
            $message->setIsclient(in_array($publication->getKeymodule()));
        }else{
            $message->setIsclient(false);
        }
*/
        $tabmsg->addIdmessage($message);
        $this->entityManager->persist($tabmsg);


        // 3 - creation du texte du message

        $msg= new MsgsP();
        /*
        if(!$dispatch && $this->session->has('tabinfoip')){ //todo a revoir me rappel plus du mecanisme ???(30/10/2021)
            $tabip=$this->session->get('tabinfoip');
            $log=new Loginner();
            $log->setAgent($this->session->get('agent'));
            $log->setIp($tabip['ip']??null);
            $log->setReferer($tabip['ref']??null);
            $log->setUri($tabip['uri']??null);
            $log->setMsg($msg);
            $msg->setMsglog($log);
        }*/

        $msg->setBodyTxt($content);
        $msg->setContentHtml(""); // todo voir html et txt
        $message->addMsg($msg);
        if($sender instanceof Activmember){
            $itemtallmesg->setLastsender($sender->getName());
        }else{
            $itemtallmesg->setLastsender($sender->getEmailCanonical());
        }
        $itemtallmesg->setExtract($content);
        $itemtallmesg->setLastconvers(new \DateTime());
        if($message->isIsmembersender()){
            $msg->setAuthormember($sender);
        }else{
            $msg->setAuthorcontact($sender);
        }
        $this->entityManager->persist($message);
        $this->entityManager->persist($itemtallmesg);
        $this->entityManager->flush();
        $event = new MessagePublicationEvent($publication, $sender, $msg,$this->boardRepository->findWbByKey($publication->getKeymodule()));

        if ($sender instanceof Activmember){
            if ($publication instanceof Post){
                $this->eventdispatcher->dispatch($event, AffiEvents::NOTIFICATION_ADD_COMMENT_POST_DISPATCH);
            }else {
                $this->eventdispatcher->dispatch($event, AffiEvents::NOTIFICATION_ADD_COMMENT_OFFRE_DISPATCH);
            }
        }else{
            if ($publication instanceof Post){
                $this->eventdispatcher->dispatch($event, AffiEvents::NOTIFICATION_ADD_COMMENT_POST_CONTACT);
            }else {
                $this->eventdispatcher->dispatch($event, AffiEvents::NOTIFICATION_ADD_COMMENT_OFFRE_CONTACT);
            }
        }
        return $message;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function addConvers($data, PublicationConvers $convers, Activmember $dispatch): MsgsP
    {
        //  $content = Tools::cleanspecialcaractere($data['comment']);
        $content = Tools::cleaninput($data['comment']);
        $msg = new MsgsP();
        $msg->setAuthormember($dispatch);
        $msg->setBodyTxt($content);
        $msg->setContentHtml("");
        $msg->setPublicationmsg($convers);
        $convers->addMsg($msg);
        $itemtallmesg=$convers->getTballmsgp();
        $itemtallmesg->setLastsender($dispatch->getName());
        $itemtallmesg->setExtract($content);
        $itemtallmesg->setLastconvers(new \DateTime());
        $this->entityManager->persist($itemtallmesg);
        $this->entityManager->persist($convers);
        $this->entityManager->flush();

        if($data['module']==="add_blog"){
            $publication=$this->repoPost->find($data['id']);
            $board=$this->boardRepository->findWbByKey($publication->getKeymodule());

            if($convers->isIsmembersender()){ // conversation entre dispatchs
                $event = new MessagePublicationEvent($publication, $dispatch, $msg, $board, $convers->getTballmsgp()->getMember());
                $this->eventdispatcher->dispatch($event, AffiEvents::NOTIFICATION_ANSWER_COMMENT_POST_DISPATCH);
            }else{ // conversation entre un/des dispath et un contact
                $event = new MessagePublicationEvent($publication, $dispatch, $msg, $board, $convers->getTballmsgp()->getContact());
                $this->eventdispatcher->dispatch($event, AffiEvents::NOTIFICATION_ANSWER_COMMENT_POST_CONTACT);
            }
        }else{
            $publication=$this->repoFrre->find($data['id']);
            $board=$this->boardRepository->findWbByKey($publication->getKeymodule());
            if($convers->isIsmembersender()){
                $event = new MessagePublicationEvent($publication, $dispatch, $msg, $board, $convers->getTballmsgp()->getMember());
                $this->eventdispatcher->dispatch($event, AffiEvents::NOTIFICATION_ANSWER_COMMENT_OFFRE_DISPATCH);
            }else{
                $event = new MessagePublicationEvent($publication, $dispatch, $msg, $board, $convers->getTballmsgp()->getContact());
                $this->eventdispatcher->dispatch($event, AffiEvents::NOTIFICATION_ANSWER_COMMENT_OFFRE_CONTACT);
            }
        }
        return $msg;
    }

    public function isClient($board, $dispatch): bool
    {
        foreach ($dispatch->getSpwsite() as $pw) {
            if ($pw->getWebsite()->getId() == $board->getId()){
                return true;
            }
        }
        return false;
    }



    public function majTabNotifs($msgs,$dispatch){
        foreach ($msgs as $mg){
            foreach ($mg->getTabreaders() as $read) {
                if (!$read->getIsread()) {
                    if ($read->getTabnotifs()->getDispatch() == $dispatch) {
                        $read->setIsread(true);
                        $tabnotif=$read->getTabnotifs();
                        $read->setTabnotifs(null);
                        $dispatch->removeTbnotif($tabnotif);
                        $this->entityManager->persist($dispatch);
                        $this->entityManager->persist($read);
                        $this->entityManager->persist($tabnotif);
                        $this->entityManager->flush();
                        $this->entityManager->remove($tabnotif);
                        $this->entityManager->flush();
                    }
                }
            }
        }
    }

    public function annulTabNotifs($idnotif,$dispatch,$em){

        $notif=$this->notifrepo->findAllBynotifId($idnotif);

        $read[]=$notif->getTabmsgp();
        $read[]=$notif->getTabmsgd();
        $read[]=$notif->getTabmsgs();
        foreach ($read as $rd){
            if($rd){
                $rd->setIsread(true);
                $rd->setTabnotifs(null);
                $dispatch->removeTbnotif($notif);
                $em->persist($dispatch);
                $em->persist($rd);
                $em->persist($notif);
                $em->flush();
                $em->remove($notif);
                $em->flush();
            }
        }
    }

    /**
     * @param $msgs
     * @param $pw Boardslist
     * @param $dispatch
     * @return mixed
     */
    public function sortMsgPublication($msgs, Boardslist $pw, $dispatch): mixed
    {
        foreach ($msgs as $key => $msg) { // on marque les message déjà lu ou dont est l'auteur
            $top=false;
            $msg->setSender(false);
            foreach ($msg->getMsgs() as $m){  //pour chaque messagges
                foreach ($m->getTabreaders() as $read) {//1 - test sur read
                    if($read->getIdispatch() == $dispatch->getId()){
                        if($read->getIsRead()){
                            $msg->setSender($read->getIsRead());
                            $top=true;
                        }
                    }
                    if($top) break;
                }
                if($top) break;
            }

            if ($pw->getRole()==='client') {  // dans le cas où le pw est un cleint ou un membre //todo pourquoi aussi les membres ?
                if (!$msg->getSpacewebexpe() || $msg->getSpacewebexpe()->getId() !== $pw->getBoard()->getId()) { // si message d'un contact ou autre que l'utilisateur
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
     * @param $msgs
     * @param $dispatch
     * @return mixed
     */
    public function sortMsgPublicationTabAll($msgs,  $dispatch): mixed
    {
        foreach ($msgs as $key => $msg) { // on marque les message déjà lu ou dont est l'auteur
            $top=false;
            $msg->setSender(false);
            foreach ($msg->getMsgs() as $m){  //pour chaque messagges
                foreach ($m->getTabreaders() as $read) {//1 - test sur read
                    if($read->getIdispatch() == $dispatch->getId()){
                        if($read->getIsRead()){
                            $msg->setSender($read->getIsRead());
                            $top=true;
                        }
                    }
                    if($top) break;
                }
                if($top) break;
            }
        }
        return $msgs;
    }

}
