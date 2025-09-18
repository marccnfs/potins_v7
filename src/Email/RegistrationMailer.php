<?php


namespace App\Email;

use App\Entity\Customer\Customers;
use App\Entity\Boards\Board;
use App\Entity\Users\ProfilUser;
use App\Entity\Users\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationMailer
{

    protected MailerSender $sender;
    protected UrlGeneratorInterface $router;
    protected array $parameters;
    private MailerSenderTest $test;


    /**
     * Mailer constructor.
     * @param MailerSender $sender
     * @param UrlGeneratorInterface $router
     */
    public function __construct(MailerSender $sender, UrlGeneratorInterface $router,MailerSenderTest $mailertest)
    {
        $this->sender = $sender;
        $this->router = $router;
        $this->test = $mailertest;
    }

    public function sendtestMail(): bool|string
    {
        $template ='aff_notification/security/test2.html.twig';
        return $this->test->SendTestMessage(
            $template,[
                'expiration_date' => new \DateTime('+7 days'),
                'username' => 'marco 1er'],
            'test messagerie potins numériques');
    }

    public function sendtestMailnoDkim(): bool|string
    {
        return $this->test->SendTestMessageNoDkim();
    }

    public function sendConfirmationEmailMessage(User $user): void
    {
        $template ='aff_notification/security/confirmation.email.twig';
        $url = $this->router->generate('registration_confirm', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=['exp' =>'les potins numeriques','dest'=>$user->getEmail(),'confirmationUrl' => $url,'user' => $user, 'msg'=>""],
            'confirmez votre inscription sur les potins numeriques',
            'registration');
    }

    public function sendConfirmationDispatchEmailMessage(User $user,Board $website, ProfilUser $profil)
    {
        $template ='aff_notification/member/invitationweb.email.twig';
        $url = $this->router->generate('dispatch_confirm', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=[
                'exp' =>'les potins numeriques',
                'dest'=>$user->getEmail(),
                'confirmationUrl' => $url,
                'user' => $user,
                'website'=>$website,
                'msg'=>""],
            'Votre invitation a rejoindre les potins numeriques',
            'registration');
    }

    public function sendConfirmationClientEmailMessage(User $user,Board $website)
    {
        $template ='aff_notification/client/byshop.email.twig';
        $url = $this->router->generate('dispatch_confirm', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=[
                'exp' =>'les potins numeriques',
                'dest'=>$user->getEmail(),
                'confirmationUrl' => $url,
                'user' => $user,
                'website'=>$website,
                'msg'=>""],
            'Votre espace est ouvert sur les potins numeriques',
            'registration');
    }

    public function sendConfirmationContactEmailMessage(User $user,Board $website)
    {
        $template ='aff_notification/contact/byconvers.email.twig';
        $url = $this->router->generate('dispatch_confirm', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=[
                'exp' =>'les potins numeriques',
                'dest'=>$user->getEmail(),
                'confirmationUrl' => $url,
                'user' => $user,
                'website'=>$website,
                'msg'=>""],
            'Votre espace est ouvert sur les potins numeriques',
            'registration');
    }


    public function sendOnvitationDispatchEmailMessage(Customers $customer,Board $website)
    {
        $template ='aff_notification/board/invit-tobecome_admin.email.twig';    // ancien :contact/invitationweb.email.twig';
        $url = $this->router->generate('app_login',[],UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=[
                'exp' =>'les potins numeriques',
                'dest'=>$customer->getEmailcontact(),
                'url'=>$url,
                'mail'=>$customer->getEmailcontact(),
                'user' => $customer,
                'website'=>$website,
                'msg'=>""],
            'Votre invitation a rejoindre :'.$website->getNameboard().'',
            'website');
    }

    public function sendOnvitationMailToBeAdmin($mail,Board $website)
    {
        $template ='aff_notification/board/invit-tobecome_admin.email.twig';
        $url = $this->router->generate('new_identify',[],UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=[
                'exp' =>'les potins numeriques',
                'dest'=>$mail,
                'url'=>$url,
                'user' => "",
                'mail'=>$mail,
                'website'=>$website,
                'msg'=>""],
            'Votre invitation a rejoindre :'.$website->getNameboard().'',
            'website');
    }

    public function sendNewPasswordEmailMessage(User $user)
    {
        $template ='aff_notification/security/reinitpassword.email.twig';
        $url = $this->router->generate('reset_change_password', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=['exp' =>'les potins numeriques','dest'=>$user->getEmail(),'confirmationUrl' => $url,'user' => $user, 'msg'=>""],
            'réinialisation mot de passe',
            'registration');
    }

    public function sendfirstWord($email, $link): bool|string
    {
        $template ='aff_notification/notifs/firstword.email.twig';
        return $this->sender->goSendMessage(
            $template,
            $context=['exp' =>'les potins numeriques','dest'=>$email,'id' => $email, 'link'=>$link, 'content' => 'merci pour tout', 'msg'=>""],
            'Premier contact, les potins numeriques',
            'prospect');
    }

    public function sendAskConfirmationDispatchEmailMessage(User $user)
    {
        $template ='aff_notification/contact/invitationweb.email.twig';
        $url = $this->router->generate('dispatch_confirm', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=['exp' =>'les potins numeriques','dest'=>$user->getEmail(),'confirmationUrl' => $url,'user' => $user, 'msg'=>""],
            'Votre invitation a rejoindre les potins numeriques',
            'registration');
    }

    public function sendResettingEmailMessage(User $user) //todo a finir
    {
        $template ='aff_notification/security/resetmail.email.twig';
        $url = $this->router->generate('resetting_reset', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=['exp' =>'les potins numeriques','dest'=>$user->getEmail(),'resetting_reset' => $url,'user' => $user, 'msg'=>""],
            'reset compte des potins numeriques',
            'registration');
    }

    public function sendAskConfirmationEmailMessage(User $user)
    {
        $template ='aff_notification/security/confirmation.email.twig';
        $url = $this->router->generate('registration_confirm', array(
            'token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->sender->goSendMessage(
            $template,
            $context=['exp' =>'les potins numeriques','dest'=>$user->getEmail(),'confirmationUrl' => $url,'user' => $user, 'msg'=>""],
            'confirmez votre inscription sur les potins numériques',
            'registration');
    }
}
