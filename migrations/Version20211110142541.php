<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211110142541 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE data_controller_gdpr (id UUID NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, siret VARCHAR(255) NOT NULL, ape VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, representative VARCHAR(255) NOT NULL, quality VARCHAR(255) NOT NULL, dpo_name VARCHAR(255) NOT NULL, dpo_email VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE gdpr_hosting (id UUID NOT NULL, company_name VARCHAR(255) NOT NULL, address VARCHAR(512) NOT NULL, representative VARCHAR(255) NOT NULL, quality VARCHAR(255) NOT NULL, siret VARCHAR(255) NOT NULL, ape VARCHAR(255) NOT NULL, company_phone VARCHAR(255) NOT NULL, company_email VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('DROP TABLE gdpr');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE gdpr (id UUID NOT NULL, company_name VARCHAR(255) NOT NULL, address VARCHAR(512) NOT NULL, representative VARCHAR(255) NOT NULL, quality VARCHAR(255) NOT NULL, siret VARCHAR(255) NOT NULL, ape VARCHAR(255) NOT NULL, company_phone VARCHAR(255) NOT NULL, company_email VARCHAR(255) NOT NULL, dpo_email VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('DROP TABLE data_controller_gdpr');
        $this->addSql('DROP TABLE gdpr_hosting');
    }
}
