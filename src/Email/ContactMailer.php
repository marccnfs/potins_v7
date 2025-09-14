<?php


namespace App\Email;


use App\Entity\Users\User;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContactMailer
{
    /**
     * @var MailerSender
     */
    private $sender;
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    public function __construct(MailerSender $sender, UrlGeneratorInterface $router)
    {
        $this->sender = $sender;
        $this->router = $router;
    }

    public function sendConfirmationEmailMessage(User $user)
    {
        $template ='desk/notifications/security/confirmation.email.twig';
        $url = $this->router->generate('registration_confirm', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $context = array(
            'user' => $user,
            'confirmationUrl' => $url,
        );
        $this->sender->goSendMessage($template, $context, $this->parameters['from_email']['confirmation'], (string) $user->getEmail());
    }


    public function sendNewNotification($provider, $contact, $body)
    {

        // DKIM

        $privatekey=file_get_contents(__DIR__ . '/dkim.private.key');

        $signer=new \Swift_Signers_DKIMSigner($privatekey,'affichange.com','email');

        // Message
        $message = (new \Swift_Message("un nouveau contact")); //todo une variante si user ou nouveau contact
        $message->attachSigner($signer)
            ->setSubject($contact['name'].' a posté un message')
            ->setFrom(array('noreply@affichange.com'=>$provider['name']))
            ->setTo($this->destinataire['canonicalemail'])
            ->setBody($body)
            ->setCharset('utf-8')
            ->setContentType('text/html')
            ->setBody($this->renderView('Admin/SwiftLayout/validationContact.html.twig',array('contact' => $contact)));


    }

    public function sendToProviderContactMessage($paramEmail)
    {
        $template ='desk/notifications/security/confirmation.email.twig';
        $context = array(
            'exp' => $paramEmail['expediteur'],
            'dest'=>$paramEmail['destinataire'],
            'linkmsg' => $paramEmail['url'],
        );
        $this->sender->goSendMessage($template, $context,'vous avez reçu un nouveau message de la part de : '.$paramEmail['expediteur']->getFirstname(),'notif');

    }
}