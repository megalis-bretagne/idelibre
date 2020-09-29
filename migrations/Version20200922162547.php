<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200922162547 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE timezone (id UUID NOT NULL, name VARCHAR(255) NOT NULL, info VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE structure ADD timezone_id UUID NOT NULL');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EA3FE997DE FOREIGN KEY (timezone_id) REFERENCES timezone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6F0137EA3FE997DE ON structure (timezone_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE structure DROP CONSTRAINT FK_6F0137EA3FE997DE');
        $this->addSql('DROP TABLE timezone');
        $this->addSql('DROP INDEX IDX_6F0137EA3FE997DE');
        $this->addSql('ALTER TABLE structure DROP timezone_id');
    }
}
