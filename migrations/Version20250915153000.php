<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Users\User;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

final class Version20250915153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Hash temporary passwords for legacy aff_user rows with NULL password values.';
    }

    public function up(Schema $schema): void
    {
        $connection = $this->connection;
        $users = $connection->fetchAllAssociative('SELECT id FROM aff_user WHERE password IS NULL');

        if ($users === []) {
            return;
        }

        $hasherFactory = new PasswordHasherFactory([
            User::class => ['algorithm' => 'auto'],
            'default' => ['algorithm' => 'auto'],
        ]);
        $userPasswordHasher = new UserPasswordHasher($hasherFactory);

        foreach ($users as $row) {
            $user = new User();
            $temporaryPassword = bin2hex(random_bytes(20));
            $hashedPassword = $userPasswordHasher->hashPassword($user, $temporaryPassword);

            $connection->executeStatement(
                'UPDATE aff_user SET password = :password WHERE id = :id',
                [
                    'password' => $hashedPassword,
                    'id' => (int) $row['id'],
                ]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Hashed passwords cannot be reverted to NULL.');
    }
}
