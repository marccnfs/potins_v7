<?php


namespace App\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;

class MailerSender
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
            case 'test':
                $this->exp='marc@potinsnumeriques.fr';
                $this->sent="affi.nbcom@gmail.com";
                return $this->sendTest();

            case 'exp': // envoi du message vers le website
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest->getTemplate()->getEmailspaceweb();
                $this->sendMessage();
                break;

            case 'notifmember': // envoi du message vers le website
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest;
                $this->sendMessage();
                break;

            case 'expmember': // envoi message d'un spaceweb vers le website // todoa revoir
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest->getTemplate()->getEmailspaceweb();
                $this->sendMessage();
                break;

            case 'op_market': // conversprivate - market - option offre -> retourne une confirmation a l'expedieur(email) et au destinataire (email)
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$expe;
                $this->sendMessage();
                break;

            case 'notif_affi': // retourne une confirmation au cleint pour le suivi de son achat
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest;
                $this->sendMessage();
                break;

            case 'website':
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest;
                $this->sendMessage();

                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest;
                $this->sendMessage();

                break;

            case 'publication':
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest;
                $this->sendMessage();
                break;

            // les autres pour resa  revoir

            case 'destmember': // retourne une confirmation a l'expedieur
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$expe->getTemplate()->getEmailspaceweb();
                $this->sendMessage();
                break;

            case 'dest': // retourne une confirmation a l'expedieur (profil)
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$expe->getEmailfirst();
                $this->sendMessage();
                break;

            case 'notif':
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$this->context['spaceweb']['template']['emailspaceweb'];
                $this->sendMessage();
                break;

            case 'registration':
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest;
                $this->sendMessage();
                return 'ok';
                break;

            case 'prospect':
                $this->exp='noreply@potinsnumeriques.fr';
                $this->sent=$dest;
                $this->sendMessage();
                return true;
                break;

            default:
                return 'probleme';
        }
        return true;
    }

    // DKIM version sur server
    public function sendMessage()
    {
        $signer=new DkimSigner($this->privatekey,'potinsnumeriques.fr','sf');
        $email = (new TemplatedEmail())
            ->from($this->exp)
            ->to(new Address($this->sent))
            ->subject($this->subjet)
            ->htmlTemplate($this->twingvue)
            ->context($this->context);
        $this->bodyrender->render($email);
        $signedEmail = $signer->sign($email);
        $this->mailer->send($signedEmail);

        return 0;
    }

    public function sendTest()
    {
        $signer=new DkimSigner($this->privatekey,'potinsnumeriques.fr','sf');
        $email = (new TemplatedEmail())
            ->from($this->exp)
            ->to(new Address($this->sent))
            ->subject($this->subjet)
            ->htmlTemplate($this->twingvue)
            ->context($this->context);
        $this->bodyrender->render($email);
        $signedEmail = $signer->sign($email);
        try {
            $this->mailer->send($signedEmail);
        } catch (TransportExceptionInterface $e) {
            return $e;
        }
        return 'ok';
    }
}