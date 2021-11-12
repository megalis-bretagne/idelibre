<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211110145751 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE data_controller_gdpr ADD structure_id UUID NOT NULL');
        $this->addSql('ALTER TABLE data_controller_gdpr ADD CONSTRAINT FK_1EBCB3692534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1EBCB3692534008B ON data_controller_gdpr (structure_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE data_controller_gdpr DROP CONSTRAINT FK_1EBCB3692534008B');
        $this->addSql('DROP INDEX UNIQ_1EBCB3692534008B');
        $this->addSql('ALTER TABLE data_controller_gdpr DROP structure_id');
    }
}
