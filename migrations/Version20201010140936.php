<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201010140936 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE sitting (id UUID NOT NULL, name VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, revision INT NOT NULL, is_archived BOOLEAN NOT NULL, place VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE convocation (id UUID NOT NULL, sitting_id UUID NOT NULL, is_read BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_active BOOLEAN NOT NULL, is_emailed BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C03B3F5F8014E66 ON convocation (sitting_id)');
        $this->addSql('CREATE TABLE timestamp (id UUID NOT NULL, convocation_id UUID DEFAULT NULL, create_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, content TEXT NOT NULL, tsa TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A5D6E63EE8746F65 ON timestamp (convocation_id)');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT FK_C03B3F5F8014E66 FOREIGN KEY (sitting_id) REFERENCES sitting (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE timestamp ADD CONSTRAINT FK_A5D6E63EE8746F65 FOREIGN KEY (convocation_id) REFERENCES convocation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT FK_C03B3F5F8014E66');
        $this->addSql('ALTER TABLE timestamp DROP CONSTRAINT FK_A5D6E63EE8746F65');
        $this->addSql('DROP TABLE sitting');
        $this->addSql('DROP TABLE convocation');
        $this->addSql('DROP TABLE timestamp');
    }
}
