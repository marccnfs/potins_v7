<?php


namespace App\Notification;

use App\Email\Sender;
use App\Entity\User;
use App\Entity\Websites\Website;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Notificationor
{
    /**
     * @var $sender
     */
    protected $sender;
    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var array
     */
    protected $parameters;


    /**
     * Mailer constructor.
     * @param Sender $sender
     * @param UrlGeneratorInterface $router
     */
    public function __construct(Sender $sender, UrlGeneratorInterface  $router)
    {
        $this->sender = $sender;
        $this->router = $router;
    }

    public function sendAskConfirmationEmailMessage(User $user)
    {
        $template ='notifications/security/confirmation.email.twig';
        $url = $this->router->generate('registration_confirm', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=['exp' =>'affichange','dest'=>$user->getEmail(),'confirmationUrl' => $url,'user' => $user, 'msg'=>""],
            'confirmez votre inscription sur affiChange',
            'registration');
    }

    public function sendAskConfirmationDispatchEmailMessage(User $user)
    {
        $template ='notifications/contact/invitationweb.email.twig';
        $url = $this->router->generate('dispatch_confirm', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=['exp' =>'affichange','dest'=>$user->getEmail(),'confirmationUrl' => $url,'user' => $user, 'msg'=>""],
            'Votre invitation a rejoindre affiChange',
            'registration');
    }

    public function sendConfirmationEmailMessage(User $user)
    {
        $template ='notifications/security/confirmation.email.twig';
        $url = $this->router->generate('registration_confirm', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=['exp' =>'affichange','dest'=>$user->getEmail(),'confirmationUrl' => $url,'user' => $user, 'msg'=>""],
            'confirmez votre inscription sur affiChange',
            'registration');
    }

    public function sendConfirmationDispatchEmailMessage(User $user,Website $website)
    {
        $template ='notifications/member/invitationweb.email.twig';
        $url = $this->router->generate('dispatch_confirm', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=[
                'exp' =>'affichange',
                'dest'=>$user->getEmail(),
                'confirmationUrl' => $url,
                'user' => $user,
                'website'=>$website,
                'msg'=>""],
            'Votre invitation a rejoindre AffiChanGe',
            'registration');
    }

    public function sendConfirmationClientEmailMessage(User $user,Website $website)
    {
        $template ='notifications/client/byshop.email.twig';
        $url = $this->router->generate('dispatch_confirm', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=[
                'exp' =>'affichange',
                'dest'=>$user->getEmail(),
                'confirmationUrl' => $url,
                'user' => $user,
                'website'=>$website,
                'msg'=>""],
            'Votre espace est ouvert sur AffiChanGe',
            'registration');
    }

    public function sendConfirmationContactEmailMessage(User $user,Website $website)
    {
        $template ='notifications/contact/byconvers.email.twig';
        $url = $this->router->generate('dispatch_confirm', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=[
                'exp' =>'affichange',
                'dest'=>$user->getEmail(),
                'confirmationUrl' => $url,
                'user' => $user,
                'website'=>$website,
                'msg'=>""],
            'Votre espace est ouvert sur AffiChanGe',
            'registration');
    }


    public function sendOnvitationDispatchEmailMessage(User $user,Website $website)
    {
        $template ='notifications/contact/invitationweb.email.twig';
        $url = $this->router->generate('tab_spaceweb', ['slug'=>$website->getSlug()]);
        $this->sender->goSendMessage(
            $template,
            $context=[
                'exp' =>'affichange',
                'dest'=>$user->getEmail(),
                'url'=>$url,
                'user' => $user,
                'website'=>$website,
                'msg'=>""],
            'Votre invitation a rejoindre :'.$website->getNamewebsite().'',
            'website');
    }

    /**
     * {@inheritdoc}
     */
    public function sendResettingEmailMessage(User $user) //todo a finir
    {
        $template ='notifications/security/resetmail.email.twig';
        $url = $this->router->generate('resetting_reset', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=['exp' =>'affichange','dest'=>$user->getEmail(),'resetting_reset' => $url,'user' => $user, 'msg'=>""],
            'reset compte affichange',
            'registration');
    }

    public function sendNewPasswordEmailMessage(User $user)
    {
        $template ='notifications/security/reinitpassword.email.twig';
        $url = $this->router->generate('reset_change_password', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=['exp' =>'affichange','dest'=>$user->getEmail(),'confirmationUrl' => $url,'user' => $user, 'msg'=>""],
            'réinialisation mot de passe',
            'registration');
    }

    public function sendfirstWord($email, $link)
    {
        $template ='notifications/notifs/firstword.email.twig';
        return $this->sender->goSendMessage(
            $template,
            $context=['exp' =>'affichange','dest'=>$email,'id' => $email, 'link'=>$link, 'content' => 'merci pour tout', 'msg'=>""],
            'Premier contact, AffiChange',
            'prospect');
    }
}