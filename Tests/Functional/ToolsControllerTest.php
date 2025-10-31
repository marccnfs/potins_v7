<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Users\Contacts;
use App\Entity\Users\ProfilUser;
use App\Entity\Users\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ToolsControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();

        $this->client = static::createClient([], ['HTTP_USER_AGENT' => 'SymfonyBrowser/1.0']);
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropDatabase();
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        if ($metadata !== []) {
            $schemaTool->createSchema($metadata);
        }
    }

    public function testTestMailEndpointsReturnNeutralResponse(): void
    {
        $knownEmail = 'existing'.uniqid('', true).'@example.test';
        $this->createUserAndContact($knownEmail);

        $this->client->jsonRequest('POST', '/toolsOld/jxrq/testContactMail', ['email' => $knownEmail]);
        self::assertResponseIsSuccessful();
        $knownResponse = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $unknownEmail = 'unknown'.uniqid('', true).'@example.test';
        $this->client->jsonRequest('POST', '/toolsOld/jxrq/testContactMail', ['email' => $unknownEmail]);
        self::assertResponseIsSuccessful();
        $unknownResponse = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame($knownResponse, $unknownResponse);

        foreach ([$knownResponse, $unknownResponse] as $payload) {
            self::assertTrue($payload['ok']);
            self::assertSame('processed', $payload['status']);
            self::assertArrayNotHasKey('email', $payload);
            self::assertArrayNotHasKey('success', $payload);
            self::assertArrayNotHasKey('contact', $payload);
        }

        $this->client->jsonRequest('POST', '/toolsOld/jxrq/test-visitor-mail', ['email' => $knownEmail]);
        self::assertResponseIsSuccessful();
        $visitorResponse = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame($unknownResponse, $visitorResponse);
    }

    public function testInvalidEmailReturnsBadRequest(): void
    {
        $this->client->jsonRequest('POST', '/toolsOld/jxrq/testContactMail', ['email' => 'not-an-email']);
        self::assertResponseStatusCodeSame(400);
        $payload = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertFalse($payload['ok']);
        self::assertArrayNotHasKey('email', $payload);
        self::assertArrayNotHasKey('success', $payload);
        self::assertArrayNotHasKey('contact', $payload);
    }

    private function createUserAndContact(string $email): void
    {
        $user = new User();
        $user->setEmail($email);
        $user->setEmailCanonical(mb_strtolower($email));
        $user->setCharte(true);
        $user->setEnabled(true);
        $user->setPassword(password_hash('Password123!', PASSWORD_BCRYPT));
        $this->entityManager->persist($user);

        $profil = new ProfilUser();
        $profil->setEmailfirst($email);

        $contact = new Contacts();
        $contact->setEmailCanonical(mb_strtolower($email));
        $contact->setUseridentity($profil);
        $this->entityManager->persist($contact);

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
