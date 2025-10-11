<?php

// src/Service/MobileLinkManager.php
namespace App\Service;

use App\Entity\Games\MobileLink;
use App\Entity\Games\EscapeGame;
use App\Entity\Users\Participant;
use Doctrine\ORM\EntityManagerInterface as EM;
use Endroid\QrCode\Builder\Builder as QrBuilder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class MobileLinkManager
{
    public function __construct(private EM $em, private UrlGeneratorInterface $urlGen) {}

    public function create(Participant $p, EscapeGame $eg, int $step, ?int $ttlMinutes = 15): MobileLink
    {
        $link = new MobileLink();
        $link->setParticipant($p);
        $link->setEscapeGame($eg);
        $link->setStep($step);
        $link->setToken(bin2hex(random_bytes(16)));
        if ($ttlMinutes === null) {
            $link->setExpiresAt(null);
        } else {
            $link->setExpiresAt((new \DateTimeImmutable())->modify("+{$ttlMinutes} minutes"));
        }
        $this->em->persist($link);
        $this->em->flush();

        return $link;
    }

    public function buildQrDataUri(MobileLink $link): string
    {
        $url = $this->urlGen->generate('mobile_entry', [
            'token' => $link->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL);
        return $this->buildQrForUrl($url);
    }

    public function buildQrForUrl(string $url): string
    {
        // Version “agnostique” v3/v4/v5: QrCode + PngWriter
        $writer = new PngWriter();

        if (class_exists(Encoding::class) && class_exists(ErrorCorrectionLevelHigh::class)) {
            // v4/v5
            $qr = QrCode::create($url)
                ->setEncoding(new Encoding('UTF-8'))
                ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
                ->setSize(280)
                ->setMargin(8);

            $result = $writer->write($qr);
            return $result->getDataUri();
        }

        // Fallback v3 (pas d’objets Encoding/ErrorCorrectionLevel*)
        $qr = new QrCode($url);
        if (method_exists($qr, 'setEncoding')) {
            $qr->setEncoding('UTF-8');
        }
        if (method_exists($qr, 'setErrorCorrectionLevel')) {
            // selon la v3, tu peux utiliser ErrorCorrectionLevel::HIGH ou la classe dédiée si dispo
            try {
                $qr->setErrorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh());
            } catch (\Throwable) {
                // ignore si non dispo
            }
        }
        $qr->setSize(280);
        $qr->setMargin(8);

        $result = $writer->write($qr);
        return $result->getDataUri();
    }
}
