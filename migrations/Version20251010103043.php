<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251010103043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agenda_events DROP CONSTRAINT fk_4460d15d876c4dda');
        $this->addSql('DROP INDEX idx_4460d15d876c4dda');
        $this->addSql('ALTER TABLE agenda_events DROP organizer_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agenda_events ADD organizer_id INT NOT NULL');
        $this->addSql('ALTER TABLE agenda_events ADD CONSTRAINT fk_4460d15d876c4dda FOREIGN KEY (organizer_id) REFERENCES aff_participant (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_4460d15d876c4dda ON agenda_events (organizer_id)');
    }
}
