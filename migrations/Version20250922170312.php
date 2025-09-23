<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250922170312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aff_customers ADD boardwbcustomer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE aff_customers ADD CONSTRAINT FK_6F5A5780671D8508 FOREIGN KEY (boardwbcustomer_id) REFERENCES aff_wbcustomers (id) NOT DEFERRABLE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6F5A5780671D8508 ON aff_customers (boardwbcustomer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aff_customers DROP CONSTRAINT FK_6F5A5780671D8508');
        $this->addSql('DROP INDEX UNIQ_6F5A5780671D8508');
        $this->addSql('ALTER TABLE aff_customers DROP boardwbcustomer_id');
    }
}
