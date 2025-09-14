<?php

namespace App\Service\Messages;

use App\AffiEvents;
use App\Entity\Member\DispatchSpaceWeb;
use App\Entity\Member\membersboard;
use App\Entity\Member\Tballmessage;
use App\Entity\LogMessages\Msgs;
use App\Entity\LogMessages\MsgBoard;
use App\Entity\LogMessages\Tbmsgs;
use App\Entity\Users\Contacts;
use App\Entity\Users\ProfilUser;
use App\Event\MessageWebsiteEvent;
use App\Lib\Tools;
use App\Repository\Entity\ContactRepository;
use App\Util\Canonicalizer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class WebsiteMessageor
{
    private EntityManagerInterface $em;
    private Canonicalizer $emailCanonicalizer;
    private ContactRepository $repoContact;
    private EventDispatcherInterface $eventdispatcher;


    public function __construct(EventDispatcherInterface $eventDispatcher, ContactRepository $contactRepository,EntityManagerInterface $entityManager,  Canonicalizer $emailCanonicalizer)
    {

        $this->em = $entityManager;
        $this->emailCanonicalizer = $emailCanonicalizer;
        $this->repoContact=$contactRepository;
        $this->eventdispatcher=$eventDispatcher;
    }

    /**
     * @param $data
     * @param $website
     * @param $client
     * @param null $dispatch
     * @return MsgBoard
     * @throws NonUniqueResultException
     */
    public function newMessageBoard($data, $website, $client, $dispatch=null): MsgBoard
    {
        $contact=null;
      //  $content = Tools::cleanspecialcaractere($data['comment']);
        $content = Tools::cleaninput($data['comment']);

        $message = new  MsgBoard();
        $itemtallmesg=new Tballmessage();

        // 1- identification de l'expediteur
        if($dispatch){
            $sender=true;
            $message->setTballmsgs($itemtallmesg);
            $itemtallmesg->setDispatch($dispatch);
            $itemtallmesg->setTballmsgs($message);
            $dispatch->addAllmessage($itemtallmesg);
            $message->setIsspaceweb(true);
            $message->setIsclient($client);
        }else{
            $sender=false;
            $email = $data['email'];
            if($data['status']!=="nomail"){
                $contact=$this->repoContact->findBymail($email);  //contact

                $identity = $contact->getUseridentity();
                $contact->setDatemajAt(new \DateTime());
            } else {
                $contact = new Contacts();
                $identity = new profilUser();
                // todo recuperation des info source du contact et validation des data formulaire
                $contact->setValidatetop(true);
                //  if (!$this->expe->getValidatetop()) return false; // todo  validation anti robot
                $identity->setFirstname("");
                $identity->setLastname("");
                $identity->setEmailfirst($email);
                $contact->setEmailCanonical($this->emailCanonicalizer->canonicalize($email)); //todo voir si necessaire de garder
                $contact->setUseridentity($identity);
                $this->em->persist($contact);
                $this->em->flush();
            }
            $itemtallmesg->setContact($contact);
            $contact->addAllmessage($itemtallmesg);
            $message->setTballmsgs($itemtallmesg);
            //$message->setContactexp($sender);
            $message->setIsspaceweb(false);
        }

        // 2 - hydratation de l'objet messagesubject

        $message->setSubject((substr($content, 0, 25)) . '...');
        $message->setWebsitedest($website);
        $website->addMsg($message);

        $msg= new Msgs();
        /*
        if(!$this->member && $this->session->has('tabinfoip')){
            $tabip=$this->session->get('tabinfoip');
            $log=new Loginner();
            $log->setAgent($this->session->get('agent'));
            $log->setIp($tabip['ip']??null);
            $log->setReferer($tabip['ref']??null);
            $log->setUri($tabip['uri']??null);
            $log->setMsg($msg);
            $msg->setMsglog($log);
        }
        */
        $msg->setBodyTxt($content);
        $msg->setContentHtml(""); // todo voir html et txt
        $message->addMsg($msg);

        if($sender){  // si dispatch
            $itemtallmesg->setLastsender($dispatch->getName());
        }else{
            $itemtallmesg->setLastsender($contact->getEmailCanonical());
        }

        $itemtallmesg->setExtract($message->getSubject());
        $itemtallmesg->setLastconvers(new \DateTime());

        if($message->getIsspaceweb()){
            $msg->setAuthordispatch($dispatch);
        }else{
            $msg->setAuthorcontact($contact);
        }
        $this->em->persist($message);
        $this->em->persist($website);
        $this->em->flush();

        $event=new MessageWebsiteEvent($website,$sender,$msg,$dispatch,$contact);
        if ($sender){
            $this->eventdispatcher->dispatch($event, AffiEvents::NOTIFICATION_ADD_MSG_WEBSITE_DISPATCH);
        }else{
            $this->eventdispatcher->dispatch($event, AffiEvents::NOTIFICATION_ADD_MSG_WEBSITE_CONTACT);
        }
        return $message;

    }

    /**
     */
    public function addConvers($data, MsgBoard $convers, DispatchSpaceWeb $dispatch): Msgs
    {
        //  $content = Tools::cleanspecialcaractere($data['comment']);
        $content = Tools::cleaninput($data['comment']);

        $msg = new Msgs();
        $msg->setAuthordispatch($dispatch);
        $msg->setBodyTxt($content);
        $msg->setContentHtml("");
        $msg->setMsgWebsite($convers);
        $convers->addMsg($msg);
        $convers->addMsg($msg);
        $itemtallmesg=$convers->getTballmsgs();
        $itemtallmesg->setLastsender($dispatch->getName());
        $itemtallmesg->setExtract($content);
        $itemtallmesg->setLastconvers(new \DateTime());
        $this->em->persist($itemtallmesg);
        $this->em->persist($convers);
        $this->em->flush();

        $board=$convers->getWebsitedest();
        if($convers->getIsspaceweb()){
            $event=new MessageWebsiteEvent($board,true,$msg,$convers->getTballmsgs()->getDispatch(),null);
            $this->eventdispatcher->dispatch($event, AffiEvents::NOTIFICATION_ANSWER_MSG_WEBSITE_DISPATCH);
        }else{
            $event=new MessageWebsiteEvent($board,false,$msg,$dispatch,$convers->getTballmsgs()->getContact());
            $this->eventdispatcher->dispatch($event, AffiEvents::NOTIFICATION_ANSWER_MSG_WEBSITE_CONTACT);
        }
        return $msg;
    }


    /**
     * @param $pagination
     * @param $pw membersboard
     * @param $dispatch
     * @return array
     */
    public function sortmesswebsite($pagination, membersboard $pw, $dispatch): array
    {
        $msgs=$pagination->getItems();
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
     * @param $pagination
     * @param $dispatch
     * @return array
     */
    public function sortmesswebsiteTabAll($pagination, $dispatch): array
    {
        $msgs=$pagination->getItems();
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


    public function majTabNotifs($msgs,$dispatch){  // todo rajout fonction si un nouveau membre prend connaissance d'ancien message
        foreach ($msgs as $mg){
            foreach ($mg->getTabreaders() as $read) {
                if (!$read->getIsread()) {
                    if ($read->getTabnotifs()->getDispatch() == $dispatch) {
                        $read->setIsread(true);
                        $tabnotif=$read->getTabnotifs();
                        $read->setTabnotifs(null);
                        $dispatch->removeTbnotif($tabnotif);
                        $this->em->persist($dispatch);
                        $this->em->persist($read);
                        $this->em->persist($tabnotif);
                        $this->em->flush();
                        $this->em->remove($tabnotif);
                        $this->em->flush();
                    }
                }
            }
        }
    }

}