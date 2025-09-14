<?php


namespace App\Service\Modules;

use App\Email\ContactMailer;
use App\Email\MailerSender;
use App\Entity\LogMessages\Msgs;
use App\Entity\Users\Contacts;
use App\Entity\Users\ProfilUser;
use App\Util\Canonicalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;

class MailatorWebsite
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var ContactMailer
     */
    private $contactMailer;

    private $expediteur;
    /**
     * @var MailerSender
     */
    private $sender;

    private $contact;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UrlGeneratorInterface
     */
    private $router;
    /**
     * @var Canonicalizer
     */
    private $emailCanonicalizer;
    private $resa;
    private $infowebsite;
    private $website;
    private $contactation;

    public function __construct(EntityManagerInterface $entityManager, Canonicalizer $emailCanonicalizer,UrlGeneratorInterface $router, Security $security, ContactMailer $contactMailer, MailerSender $sender)
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->security = $security;
        $this->contactMailer = $contactMailer;
        $this->sender = $sender;
        $this->emailCanonicalizer = $emailCanonicalizer;
        $this->contact=null;
    }

    public function newMessage($parametre){

        $this->resa=$parametre['resa'];
        $this->infowebsite=$parametre['websitetab']; //un array
        $this->website=$parametre['website']; //objet
        $this->contactation=$parametre['contactation'];
        $messageSubject=$parametre['message'];

        // 1- identification de l'expediteur
        if($parametre['spacewebexp']) {
            $member=true;
            $messageSubject->setSpacewebexpe($parametre['spacewebexp']);
            $messageSubject->setIsspaceweb(true);
            $this->expediteur = $parametre['spacewebexp'];
        }
        else{
            $member=false;
            if($parametre['contact']){
            $this->contact=$parametre['contact'];
            $identity=$this->contact->getUseridentity();
            $this->contact->setDatemajAt(new \DateTime());
            }else{
            $this->contact = new Contacts();
            $identity=new profilUser();
            }
            // todo recuperation des info source du contact et validation des data formulaire
            $this->contact->setValidatetop(true);
            if(!$this->contact->getValidatetop())return false; // todo  validation anti robot
            //if(!$this->resa){$this->contact->setSectorcontact($parametre['form']->get('sectorcode')->getData());}
// desactive le sector pour l'instant
                                    //$code=mb_substr($valuecity,strpos($valuecity,'('),5); todo revoir comprehension de ce code
            $identity->setFirstname($parametre['form']->get('username')->getData());
            $identity->setTelephonemobile($parametre['form']->get('telephone')->getData());
            $identity->setLastname("");
            $email=$parametre['form']->get('email')->getData();
            $identity->setEmailfirst($email);
            $this->contact->setEmailCanonical($this->emailCanonicalizer->canonicalize($email)); //todo voir si necessaire de garder
            $this->contact->setUseridentity($identity);
            $this->entityManager->persist($this->contact);
            $this->entityManager->flush();
            $this->expediteur=$identity;
            $messageSubject->setContactexp($this->contact);
            $messageSubject->setIsspaceweb(false);
            $messageSubject->setSender(true);
        }


        // 2 - hydratation de l'objet messagesubject

        $messageSubject->setSubject($this->contactation->getIdmodule()->getTypemodule());
        $messageSubject->setWebsitedest($this->website);
        $messageSubject->setIsSenderRead(false);
        $messageSubject->setIsDestRead(false);
        $messageSubject->setSenderRemoved(false);
        $messageSubject->setDestRemoved(false);
        $this->contactation->addMessage($messageSubject);
        $this->entityManager->persist($this->contactation);
        $this->entityManager->flush();

        // 3 - creation du message ( BD ) pas du mail !

        $message = new Msgs();
        // todo option en function du module(fields formulaire contact, fields reservation...)

        if($this->resa){
            $this->resa->setMessage($messageSubject);
            $msgresa="Réservation de : ".$this->expediteur." du : ".$this->resa->getDatecreAt()->format('d-m-Y')." pour le : ".$this->resa->getDateresaAt()->format('d-m-Y')." pour ".$this->resa->getNbcouverts()." couvert en salle :".$this->resa->getSalle();
            $msgresa .="<br>Commentaire : ".$this->resa->getCommentaire();

        }else{
            $msgresa="message : ";
            /* disable this party for this moment
            foreach ($parametre['form']->get('energies')->getData() as $key => $value){
                $msgresa .=$value.$key===count($parametre['form']->get('energies')->getData())-1?", ":".";
            }
            */
            $msgresa .="<br>".$parametre['form']->get('content')->getData();
        }
        $message->setBodyTxt($msgresa);
        $message->setContentHtml(""); // todo voir html et txt
        $message->setOwnerOfSubject(true); //l'expediteur est a l'origine du message (ce n'est pas une reponse)
        $messageSubject->addIdMessage($message);
        $this->entityManager->persist($messageSubject);
        $this->entityManager->flush();

        // 4 - generation du lien dans le mail pour ouvrir la page de messagerie et visualiser le message

        $url = $this->router->generate('read_msg', [
            'slug'=>$this->infowebsite['slug'],
            'id' => $messageSubject->getId()],
            //'name' => urlencode($parametre['form']->get('name')->getData(true))],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // 5 - preparation du mail (paramètres) et envoie via sender->gosendmessage

        if($this->resa){
            if($member){
                $issue=$this->sender->goSendMessage(
                    'mail/notifresa.email.twig',
                    $context=['exp' => $this->expediteur,'spaceweb'=>$this->infowebsite,'linkmsg' => $url,'resa'=>$this->resa, 'msg'=>$message->getBodyTxt()],
                    'vous avez reçu une nouvelle réservation de : '.$this->expediteur->getName(),
                    'expmember');
                $issue=$this->sender->goSendMessage(
                    'mail/confirmresa1.email.twig',
                    $context=['spaceweb' =>$this->infowebsite,'exp'=>$this->expediteur,'linkmsg' => $url,'resa'=>$this->resa],
                    'confirmation de votre réservation',
                    'destmember');
            }else{
                $issue=$this->sender->goSendMessage(
                    'mail/notifresa.email.twig',
                    $context=['exp' => $this->expediteur,'spaceweb'=>$this->infowebsite,'linkmsg' => $url,'resa'=>$this->resa, 'msg'=>$message->getBodyTxt()],
                    'vous avez reçu une nouvelle réservation de : '.$this->expediteur->getFirstname(),
                    'exp');
                $issue=$this->sender->goSendMessage(
                    'mail/confirmresa1.email.twig',
                    $context=['spaceweb' =>$this->infowebsite,'exp'=>$this->expediteur,'linkmsg' => $url,'resa'=>$this->resa],
                    'confirmation de votre réservation',
                    'dest');
            }

        }else{
            if($member){
                $issue=$this->sender->goSendMessage(
                    'mail/notifmsgcontact.email.twig',
                    $context=['exp' =>$this->expediteur,'spaceweb'=>$this->infowebsite,'linkmsg' => $url, 'msg'=>$message->getBodyTxt()],
                    'vous avez reçu un nouveau message de la part de : '.$this->expediteur->getName(),
                    'expmember');
            }else{
            $issue=$this->sender->goSendMessage(
                'mail/notifmsgcontact.email.twig',
                $context=['exp' =>$this->expediteur,'spaceweb'=>$this->infowebsite,'linkmsg' => $url, 'msg'=>$message->getBodyTxt()],
                'vous avez reçu un nouveau message de la part de : '.$this->expediteur->getFirstname(),
                'exp');
            }
        }
        if($issue)return $parties=["exp"=>$this->contact, "dest"=>$this->infowebsite['name']];
        return  false;
    }
}