<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211020134925 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE api_role (id UUID NOT NULL, name VARCHAR(255) NOT NULL, composites JSONB NOT NULL, pretty_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE api_user (id UUID NOT NULL, structure_id UUID NOT NULL, api_role_id UUID NOT NULL, name VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AC64A0BA2534008B ON api_user (structure_id)');
        $this->addSql('CREATE INDEX IDX_AC64A0BA3B3D56CB ON api_user (api_role_id)');
        $this->addSql('ALTER TABLE api_user ADD CONSTRAINT FK_AC64A0BA2534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE api_user ADD CONSTRAINT FK_AC64A0BA3B3D56CB FOREIGN KEY (api_role_id) REFERENCES api_role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE api_user DROP CONSTRAINT FK_AC64A0BA3B3D56CB');
        $this->addSql('DROP TABLE api_role');
        $this->addSql('DROP TABLE api_user');
    }
}
