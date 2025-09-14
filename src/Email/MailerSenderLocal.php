<?php


namespace App\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Crypto\DkimSigner;

class MailerSenderLocal
{
    private $subjet;
    private $twingvue;
    private $context;
    private $mailer;
    private $exp;
    private $sent;
    private TransportInterface $transport;
    private $privatekey;
    private BodyRendererInterface $bodyrender;

    public function __construct(MailerInterface $mailer,TransportInterface $transport,BodyRendererInterface $bodyRenderer)
    {
        $this->mailer = $mailer;
        $this->transport = $transport;
        $this->bodyrender=$bodyRenderer;
        $this->privatekey=file_get_contents(__DIR__ . '/dkim.private.key');
    }

    /**
     * @param $twingvue
     * @param $context
     * @param $subjet
     * @param $type
     * @return bool|string
     */
    public function goSendMessage($twingvue, $context, $subjet, $type): bool|string
    { // todo pour faire une verif des contenus et validation
        $this->context=$context;
        $this->twingvue=$twingvue;
        $this->subjet=$subjet;
        $expe=$this->context['exp'];
        $dest=$this->context['dest'];

        switch ($type){
            case 'exp': // envoi du message vers le website
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest->getTemplate()->getEmailspaceweb();
                $this->sendlocaltransport();
                break;

            case 'notifmember': // envoi du message vers le website
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest;
                $this->sendlocaltransport();
                break;

            case 'expmember': // envoi message d'un spaceweb vers le website // todoa revoir
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest->getTemplate()->getEmailspaceweb();
                $this->sendlocaltransport();
                break;

            case 'op_market': // conversprivate - market - option offre -> retourne une confirmation a l'expedieur(email) et au destinataire (email)
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$expe;
                $this->sendlocaltransport();
                break;

            case 'notif_affi': // retourne une confirmation au cleint pour le suivi de son achat
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest;
                $this->sendlocaltransport();
                break;

            case 'website':
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest;
                $this->sendlocaltransport();

                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest;
                $this->sendlocaltransport();

                break;



            case 'publication':
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest;
                $this->sendlocaltransport();
                break;

            // les autres pour resa  revoir

            case 'destmember': // retourne une confirmation a l'expedieur
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$expe->getTemplate()->getEmailspaceweb();
                $this->sendlocaltransport();
                break;

            case 'dest': // retourne une confirmation a l'expedieur (profil)
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$expe->getEmailfirst();
                $this->sendlocaltransport();
                break;

            case 'notif':
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$this->context['spaceweb']['template']['emailspaceweb'];
                $this->sendlocaltransport();
                break;

            case 'registration':
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest;
                $this->sendlocaltransport();
                return 'ok';
                break;

            case 'prospect':
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest;
                $this->sendlocaltransport();
                return true;
                break;

            default:
                return 'probleme';
        }
        return true;
    }


    public function testMessage()
    {
        $email = (new Email())
            ->from('hello@example.com')
            ->to('conseiller.numeriquesjb@gmail.com.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $this->mailer->send($email);
    }


    public function sendMessage()
    {

        $signer=new DkimSigner($this->privatekey,'affichange.com','default');
        $email = (new TemplatedEmail())
            ->from($this->exp)
            ->To($this->sent)
            ->subject($this->subjet)
            ->htmlTemplate($this->twingvue)
            ->context($this->context);
        // DKIM version sur server

        $this->bodyrender->render($email);
        $signedEmail = $signer->sign($email);
        $this->mailer->send($signedEmail);

        return 0;
    }


    public function sendlocal(): int
    {
        $email = (new TemplatedEmail())
            ->from($this->exp)
            ->To($this->sent)
            ->subject($this->subjet)
            ->htmlTemplate($this->twingvue)
            ->context($this->context);

        $this->bodyrender->render($email);
        $this->mailer->send($email);

        return 0;
    }

    public function sendlocaltransport(): int
    {
        $email = (new TemplatedEmail())
            ->from($this->exp)
            ->To($this->sent)
            ->subject($this->subjet)
            ->htmlTemplate($this->twingvue)
            ->context($this->context);

        $this->bodyrender->render($email);
        try {
            $this->transport->send($email);
        } catch (TransportExceptionInterface $e) {
            // some error prevented the email sending; display an
            // error message or try to resend the message
        }
        return 0;
    }
}