<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201012064927 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE convocation ADD actor_id UUID NOT NULL');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT FK_C03B3F5F10DAF24A FOREIGN KEY (actor_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C03B3F5F10DAF24A ON convocation (actor_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT FK_C03B3F5F10DAF24A');
        $this->addSql('DROP INDEX IDX_C03B3F5F10DAF24A');
        $this->addSql('ALTER TABLE convocation DROP actor_id');
    }
}
