<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200922133125 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "group" (id UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6DC044C55E237E06 ON "group" (name)');
        $this->addSql('CREATE TABLE structure (id UUID NOT NULL, group_id UUID DEFAULT NULL, name VARCHAR(255) NOT NULL, reply_to VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6F0137EA5E237E06 ON structure (name)');
        $this->addSql('CREATE INDEX IDX_6F0137EAFE54D947 ON structure (group_id)');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, structure_id UUID DEFAULT NULL, group_id UUID DEFAULT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, roles JSONB NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON "user" (username)');
        $this->addSql('CREATE INDEX IDX_8D93D6492534008B ON "user" (structure_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649FE54D947 ON "user" (group_id)');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EAFE54D947 FOREIGN KEY (group_id) REFERENCES "group" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D6492534008B FOREIGN KEY (structure_id) REFERENCES structure (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649FE54D947 FOREIGN KEY (group_id) REFERENCES "group" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE structure DROP CONSTRAINT FK_6F0137EAFE54D947');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649FE54D947');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D6492534008B');
        $this->addSql('DROP TABLE "group"');
        $this->addSql('DROP TABLE structure');
        $this->addSql('DROP TABLE "user"');
    }
}
