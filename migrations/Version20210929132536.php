<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210929132536 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE reminder (id UUID NOT NULL, sitting_id UUID DEFAULT NULL, type_id UUID DEFAULT NULL, duration INT NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_40374F408014E66 ON reminder (sitting_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_40374F40C54C8C93 ON reminder (type_id)');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F408014E66 FOREIGN KEY (sitting_id) REFERENCES sitting (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F40C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE party_legacy_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('DROP TABLE reminder');
    }
}
