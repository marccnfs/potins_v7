<?php


namespace App\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\HttpKernel\KernelInterface;

class MailerSender
{
    private TransportInterface $transport;
    private $privatekey;
    private BodyRendererInterface $bodyrender;
    private $env;
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer,TransportInterface $transport,BodyRendererInterface $bodyRenderer, KernelInterface $kernel)
    {
        $this->mailer = $mailer;
        $this->transport = $transport;
        $this->bodyrender=$bodyRenderer;
        $this->privatekey=file_get_contents(__DIR__ . '/dkim.private.key');
        $this->env = $kernel->getEnvironment();
    }

    public function goSendMessage($twingvue, $context, $subjet): bool|string
    {

        $email = (new TemplatedEmail())
            ->from(new Address('contact@potinsnumeriques.fr', 'potins mail Bot'))
            ->to((string)$context['user']->getEmail())
            ->subject($subjet)
            ->htmlTemplate($twingvue)
            ->context($context);
            $this->bodyrender->render($email);

        if ($this->env === 'prod') {
            $signer = new DkimSigner($this->privatekey, 'potinsnumeriques.fr', 'sf');
            $signedEmail = $signer->sign($email);
            $this->mailer->send($signedEmail);
        } else {
            // En dev : envoi sans DKIM
            $this->mailer->send($email);
        }
        return 0;
    }
}
