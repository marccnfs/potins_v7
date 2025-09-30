<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250924175457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aff_playsession ADD progress_steps JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE aff_playsession ADD current_step INT DEFAULT NULL');
        $this->addSql('ALTER TABLE aff_playsession ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aff_playsession DROP progress_steps');
        $this->addSql('ALTER TABLE aff_playsession DROP current_step');
        $this->addSql('ALTER TABLE aff_playsession DROP updated_at');
    }
}
