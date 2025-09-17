<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Boards\Board;
use App\Entity\Member\Activmember;
use App\Entity\Member\Boardslist;
use App\Entity\Users\User;
use App\Repository\UserRepository;
use App\Service\Gestion\AutoCommande;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MemberRegistrationTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();

        $this->client = static::createClient([], ['HTTP_USER_AGENT' => 'SymfonyBrowser/1.0']);
        $container = static::getContainer();

        $mockAutoCommande = $this->createMock(AutoCommande::class);
        $mockAutoCommande->method('newInscriptionCmd')->willReturn(true);
        $container->set(AutoCommande::class, $mockAutoCommande);

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropDatabase();
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        if ($metadata !== []) {
            $schemaTool->createSchema($metadata);
        }
    }

    public function testMemberRegistrationAndDashboardAccess(): void
    {
        $email = 'member'.uniqid('', true).'@example.test';
        $plainPassword = 'Password123!';

        $this->client->request('GET', '/security/admin/create-board-stape-mediator');
        $this->client->submitForm('Go', [
            'user[email]' => $email,
            'user[plainPassword][first]' => $plainPassword,
            'user[plainPassword][second]' => $plainPassword,
            'user[charte]' => 1,
            'user[contact]' => '',
        ]);

        self::assertResponseRedirects('/registration-confirmee');
        $this->client->followRedirect();

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findUserByEmail(strtolower($email));
        self::assertInstanceOf(User::class, $user);

        $passwordHash = $user->getPassword();
        self::assertNotEmpty($passwordHash);
        self::assertNotSame($plainPassword, $passwordHash);
        self::assertNotSame(0, password_get_info($passwordHash)['algo']);

        $user->setEnabled(true);
        $customer = $user->getCustomer();
        $customer->setCharte(true);

        $member = new Activmember();
        $member->setName('Test Member '.uniqid());
        $member->setPermission([]);
        $member->setCustomer($customer);

        $board = new Board();
        $board->setNameboard('Test Board '.uniqid());
        $board->setCodesite('code'.uniqid());
        $board->setSlug('board-'.uniqid());

        $boardslist = new Boardslist();
        $boardslist->setRole('admin');
        $boardslist->setIsadmin(true);
        $boardslist->setIsdefault(true);
        $boardslist->setToken(bin2hex(random_bytes(6)));
        $board->addBoardslist($boardslist);
        $member->addBoardslist($boardslist);

        $customer->setMember($member);

        $this->entityManager->persist($board);
        $this->entityManager->persist($member);
        $this->entityManager->persist($boardslist);
        $this->entityManager->persist($customer);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/tableau-de-bord');

        self::assertResponseIsSuccessful();
    }
}
