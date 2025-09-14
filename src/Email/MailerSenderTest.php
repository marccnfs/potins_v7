<?php


namespace App\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Email;

class MailerSenderTest
{
    private MailerInterface $mailer;
    private string|false $privatekey;
    private BodyRendererInterface $bodyrender;

    public function __construct(MailerInterface $mailer,BodyRendererInterface $bodyRenderer)
    {
        $this->mailer = $mailer;
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
    public function SendTestMessage($twingvue, $context, $subjet): bool|string
    {
        $context1 =$context;
        $twingvue1 =$twingvue;
        $subjet1 =$subjet;

        $signer=new DkimSigner($this->privatekey,'potinsnumeriques.fr','sf');
        $email = (new TemplatedEmail())
            ->from("marc@potinsnumeriques.fr")
            ->to(new Address("marc.de-jesus@conseiller-numerique.fr", 'marc'))
            ->subject($subjet1)
            ->htmlTemplate($twingvue1)
            ->context($context1);

        $this->bodyrender->render($email);
        $signedEmail = $signer->sign($email);
        try {
            $this->mailer->send($signedEmail);
        } catch (TransportExceptionInterface $e) {
            return $e;
        }
        return 'ok';
    }

    public function SendTestMessageNoDkim(): bool|string
    {
       // $transport = Transport::fromDsn('smtp://localhost:25');
       // $mailer = new Mailer($transport);

        $email = (new Email())
            ->from('hello@potinsnumeriques.fr')
            ->to('marc.de-jesus@conseiller-numerique.fr')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $this->mailer->send($email);
        return 'ok';
    }

}