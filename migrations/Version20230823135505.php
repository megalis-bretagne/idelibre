<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230823135505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE convocation ADD mandator_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE convocation DROP deputy');
        $this->addSql('COMMENT ON COLUMN convocation.mandator_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT FK_C03B3F5FF0C46DC5 FOREIGN KEY (mandator_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C03B3F5FF0C46DC5 ON convocation (mandator_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT FK_C03B3F5FF0C46DC5');
        $this->addSql('DROP INDEX IDX_C03B3F5FF0C46DC5');
        $this->addSql('ALTER TABLE convocation ADD deputy VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE convocation DROP mandator_id');
    }
}
