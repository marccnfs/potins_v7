<?php


namespace App\Service\Modules;


use App\Email\MailerSender;
use App\Entity\Boards\Board;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class Mailator
{
    private MailerSender $sender;
    private UrlGeneratorInterface $router;


    public function __construct( UrlGeneratorInterface $router,  MailerSender $sender)
    {
        $this->router = $router;
        $this->sender = $sender;
    }

    public function newMail(Board $wb, $expe, $member, $msg): void
    {

        // generation du lien dans le mail pour ouvrir la page de messagerie et visualiser le message

        $url = $this->router->generate('read_msg', [
            'slug'=>$wb->getSlug(),
            'id' => $msg->getId()],
            //'name' => urlencode($parametre['form']->get('name')->getData(true))],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // preparation du mail (paramètres) et envoie via sender->gosendmessage

        if($member){
            $this->sender->goSendMessage(
            'aff_notification/contact/notifmsgcontact.email.twig',
            $context=['exp' =>$expe,'dest'=>$wb,'linkmsg' => $url, 'msg'=>$msg->getSubject()],
            'vous avez reçu un nouveau message de la part de : '.$expe->getName(),
            'expmember');
            return ;
        }else{
            $this->sender->goSendMessage(
            'aff_notification/contact/notifmsgcontact.email.twig',
            $context=['exp' =>$expe,'dest'=>$wb,'linkmsg' => $url, 'msg'=>$msg->getSubject()],
            'vous avez reçu un nouveau message de la part de : '.$expe->getFirstname(),
            'exp');
            return ;
        }
    }


    public function notifMessageMemberwebsite(Board $website, $expediteur, $message): void
    {
        $url = $this->router->generate('read_msg', [
            'slug'=>$website->getSlug(),
            'id' => $message->getId()],
            //'name' => urlencode($parametre['form']->get('name')->getData(true))],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $this->sender->goSendMessage(
            'aff_notification/notifs/newMsgwebsiteMembers.email.twig',
            $context=['exp' =>$website,'dest'=>$expediteur,'linkmsg' => $url, 'msg'=>$message->getSubject()],
            'Un nouveau message sur : '.$website->getNameboard(),
            'notifmember');
        return ;
    }


    public function reponseMailContact($webitemsg, $msg): void
    {
        $url = $this->router->generate('show_website', [
            'city'=>$webitemsg->getWebsitedest()->getLocality()->getSlugcity(),
            'nameboard' => $webitemsg->getWebsitedest()->getSlug()],
            //'name' => urlencode($parametre['form']->get('name')->getData(true))],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        //goSendMessage($twingvue, $context, $subjet, $type)
        $this->sender->goSendMessage(
            'aff_notification/contact/reponse.email.twig',
            $context=['dest' =>$webitemsg->getWebsitedest(),'exp'=>$webitemsg->getContactexp()->getUseridentity(),'linkmsg' => $url, 'msg'=>$msg],
            'vous avez reçu un nouveau message de la part de : '.$webitemsg->getWebsitedest()->getNamewebsite(),
            'dest');
        return ;

    }


    public function reponseMailToClient($website, $message, $client): void
    {
        $url = $this->router->generate('read_msg', [
            'slug'=>$website->getSlug(),
            'id' => $message->getId()],
            //'name' => urlencode($parametre['form']->get('name')->getData(true))],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        //goSendMessage($twingvue, $context, $subjet, $type)
        $this->sender->goSendMessage(
            'aff_notification/notifs/newMsgwebsiteMembers.email.twig',
            $context=['dest' =>$client->getCustomer()->getProfil()->getEmailfirst(),'exp'=>$website,'linkmsg' => $url, 'msg'=>""],
            'vous avez reçu un nouveau message de la part de : '.$website->getNameboard(),
            'notifmember');
        return ;
    }


    public function notifiNewConversDestAndExpe($offre, $client, $convers): void
    {

        $url = $this->router->generate('read_private_convers_dp', [
            'id' => $convers->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $dest=$offre->getIsotherdest()?$offre->getDestinataire():$offre->getAuthor()->getCustomer()->getEmailcontact();
        $this->sender->goSendMessage(
            'aff_notification/market/notifmsgseller.email.twig',
            ['exp' =>$client->getCustomer()->getEmailcontact(),'dest'=>$dest,'linkmsg' => $url,'offre'=>$offre, 'msg'=>$convers->getSubject()],
            'vous avez reçu un nouveau message concerant votre offre : '.$offre->getTitre(),
            'op_market');

        $this->sender->goSendMessage(
            'aff_notification/market/notifmsgclient.email.twig',
            ['exp' =>'','dest'=>$client->getCustomer()->getEmailcontact(),'linkmsg' => $url, 'offre'=>$offre,'msg'=>$convers->getSubject()],
            'Suivi de votre conversation sur : '.$offre->getTitre(),
            'notif_affi');
        return ;
    }


    public function notifiNewPrivateConversDestAndExpe($expe, $dest, $convers): void
    {

        $url = $this->router->generate('read_private_convers_dp', [
            'id' => $convers->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $dest=$dest->getCustomer()->getEmailcontact();
        $this->sender->goSendMessage(
            'aff_notification/market/notifmsgseller.email.twig',
            ['exp' =>$expe->getCustomer()->getEmailcontact(),'dest'=>$dest,'linkmsg' => $url,'msg'=>$convers->getSubject()],
            'vous avez reçu un nouveau message privé ',
            'private');

    }

    /**
     * @param $publication
     * @param $contact
     * @param $message
     */
    public function confirAddCommentPublicationToContact($publication, $contact, $message): void
    {

        $url = $this->router->generate('new_identify_stape_usager', [
            'id' => $contact->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $this->sender->goSendMessage(
            'aff_notification/publications/confirm-first-comment-contact.email.twig',
            ['exp' =>$publication,'dest'=>$contact->getEmailCanonical(),'url' => $url,'msg'=>$message],
            "Votre message sur les potins numeriques",
            'publication');
    }

    public function confirAddCommentWebsiteToContact($board,$contact, $message): void
    {



        $url = $this->router->generate('new_identify_stape_usager', [
            'id' => $contact->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $this->sender->goSendMessage(
            'aff_notification/board/anserw-comment-contact.email.twig',
            ['exp' =>$board->getNamewebsite(),'dest'=>$contact->getEmailCanonical(),'url' => $url,'msg'=>$message],
            "Votre message sur AffiChange",
            'website');
    }

    public function notifMailNewCommentWebsiteByContact($board,$contact, $message): void
    {
        // generation du lien dans le mail pour ouvrir la page de messagerie et visualiser le message



        $url = $this->router->generate('read_msg_board', [
            'slug'=>$board->getSlug(),
            'id' => $message->getMsgwebsite()->getId()],
            //'name' => urlencode($parametre['form']->get('name')->getData(true))],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->sender->goSendMessage(
            'aff_notification/contact/notifmsgcontact.email.twig',
            $context=['exp' =>$contact,'dest'=>$board,'linkmsg' => $url, 'msg'=>$message->getMsgwebsite()->getSubject()],
            'vous avez reçu un nouveau message de la part de : '.$contact->getEmailcanonical(),
            'exp');
    }


    public function informCreatePartnerToContact( $event): void
    {

        $url = $this->router->generate('suggest_boardpartner', [
            'id' => $event->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $this->sender->goSendMessage(
            'aff_notification/partner/info-create-partner.email.twig',
            ['exp' =>"",'dest'=>$event->getContact()->getEmailCanonical(),'url' => $url,'invitor'=>$event->getBoard(), 'partner'=>$event->getPartner(), 'tabsuggets'=>$event],
            "".$event->getBoard()->getNamewebsite()."souhaite ajouter le panneau ".$event->getPartner()->getNamewebsite()." comme Partenaire",
            'partner');
    }
}