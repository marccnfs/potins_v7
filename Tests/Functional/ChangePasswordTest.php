<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Users\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ChangePasswordTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

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

        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
    }

    public function testPasswordIsUpdatedThroughResetForm(): void
    {
        $user = new User();
        $email = 'user'.uniqid('', true).'@example.test';
        $user->setEmail($email);
        $user->setEmailCanonical(strtolower($email));
        $user->setCharte(true);
        $user->setEnabled(true);

        $user->setPassword($this->passwordHasher->hashPassword($user, 'InitialPassword123!'));
        $initialHash = $user->getPassword();

        $token = bin2hex(random_bytes(16));
        $user->setConfirmationToken($token);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->request('GET', '/security/oderder/profil-password/reset-change-password', ['token' => $token]);

        $newPassword = 'NewPassword456!';
        $this->client->submitForm('validez', [
            'change_password[plainPassword][first]' => $newPassword,
            'change_password[plainPassword][second]' => $newPassword,
        ]);

        self::assertResponseIsSuccessful();

        $this->entityManager->clear();

        $updatedUser = $this->entityManager->getRepository(User::class)->findOneBy(['emailCanonical' => strtolower($email)]);
        self::assertInstanceOf(User::class, $updatedUser);
        self::assertNotSame($initialHash, $updatedUser->getPassword());
        self::assertTrue(password_verify($newPassword, $updatedUser->getPassword()));
        self::assertNull($updatedUser->getPlainPassword());
    }
}
