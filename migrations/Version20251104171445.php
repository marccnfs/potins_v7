<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251104171445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ar_scene ADD content_type VARCHAR(32) NOT NULL');
        $this->addSql('ALTER TABLE ar_scene ADD position_x DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE ar_scene ADD position_y DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE ar_scene ADD position_z DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE ar_scene ADD rotation_x DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE ar_scene ADD rotation_y DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE ar_scene ADD rotation_z DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE ar_scene ADD scale_x DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE ar_scene ADD scale_y DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE ar_scene ADD scale_z DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE ar_scene ADD share_token VARCHAR(64) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E0171CABD6594DD6 ON ar_scene (share_token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_E0171CABD6594DD6');
        $this->addSql('ALTER TABLE ar_scene DROP content_type');
        $this->addSql('ALTER TABLE ar_scene DROP position_x');
        $this->addSql('ALTER TABLE ar_scene DROP position_y');
        $this->addSql('ALTER TABLE ar_scene DROP position_z');
        $this->addSql('ALTER TABLE ar_scene DROP rotation_x');
        $this->addSql('ALTER TABLE ar_scene DROP rotation_y');
        $this->addSql('ALTER TABLE ar_scene DROP rotation_z');
        $this->addSql('ALTER TABLE ar_scene DROP scale_x');
        $this->addSql('ALTER TABLE ar_scene DROP scale_y');
        $this->addSql('ALTER TABLE ar_scene DROP scale_z');
        $this->addSql('ALTER TABLE ar_scene DROP share_token');
    }
}
