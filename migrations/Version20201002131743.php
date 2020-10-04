<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201002131743 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE email_template (id UUID NOT NULL, structure_id UUID NOT NULL, type_id UUID DEFAULT NULL, name VARCHAR(255) NOT NULL, content TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9C0600CA5E237E06 ON email_template (name)');
        $this->addSql('CREATE INDEX IDX_9C0600CA2534008B ON email_template (structure_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9C0600CAC54C8C93 ON email_template (type_id)');
        $this->addSql('ALTER TABLE email_template ADD CONSTRAINT FK_9C0600CA2534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE email_template ADD CONSTRAINT FK_9C0600CAC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE email_template');
    }
}
