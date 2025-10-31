<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251031162916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ar_pack ADD thumbnail VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE ar_pack RENAME COLUMN path_mind TO mind_path');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ar_pack DROP thumbnail');
        $this->addSql('ALTER TABLE ar_pack RENAME COLUMN mind_path TO path_mind');
    }
}
