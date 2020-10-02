<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201002104055 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, structure_id UUID DEFAULT NULL, group_id UUID DEFAULT NULL, role_id UUID DEFAULT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON "user" (username)');
        $this->addSql('CREATE INDEX IDX_8D93D6492534008B ON "user" (structure_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649FE54D947 ON "user" (group_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649D60322AC ON "user" (role_id)');
        $this->addSql('CREATE TABLE timezone (id UUID NOT NULL, name VARCHAR(255) NOT NULL, info VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE role (id UUID NOT NULL, name VARCHAR(255) NOT NULL, composites JSONB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_57698A6A5E237E06 ON role (name)');
        $this->addSql('CREATE TABLE structure (id UUID NOT NULL, group_id UUID DEFAULT NULL, timezone_id UUID NOT NULL, name VARCHAR(255) NOT NULL, reply_to VARCHAR(255) NOT NULL, suffix VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6F0137EA5E237E06 ON structure (name)');
        $this->addSql('CREATE INDEX IDX_6F0137EAFE54D947 ON structure (group_id)');
        $this->addSql('CREATE INDEX IDX_6F0137EA3FE997DE ON structure (timezone_id)');
        $this->addSql('CREATE TABLE type (id UUID NOT NULL, structure_id UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8CDE57292534008B ON type (structure_id)');
        $this->addSql('CREATE TABLE type_user (type_id UUID NOT NULL, user_id UUID NOT NULL, PRIMARY KEY(type_id, user_id))');
        $this->addSql('CREATE INDEX IDX_5A9C1341C54C8C93 ON type_user (type_id)');
        $this->addSql('CREATE INDEX IDX_5A9C1341A76ED395 ON type_user (user_id)');
        $this->addSql('CREATE TABLE "group" (id UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6DC044C55E237E06 ON "group" (name)');
        $this->addSql('CREATE TABLE forget_token (id UUID NOT NULL, user_id UUID NOT NULL, token VARCHAR(255) NOT NULL, expire_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_51C96252A76ED395 ON forget_token (user_id)');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D6492534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649FE54D947 FOREIGN KEY (group_id) REFERENCES "group" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EAFE54D947 FOREIGN KEY (group_id) REFERENCES "group" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EA3FE997DE FOREIGN KEY (timezone_id) REFERENCES timezone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE type ADD CONSTRAINT FK_8CDE57292534008B FOREIGN KEY (structure_id) REFERENCES structure (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE type_user ADD CONSTRAINT FK_5A9C1341C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE type_user ADD CONSTRAINT FK_5A9C1341A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE forget_token ADD CONSTRAINT FK_51C96252A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE type_user DROP CONSTRAINT FK_5A9C1341A76ED395');
        $this->addSql('ALTER TABLE forget_token DROP CONSTRAINT FK_51C96252A76ED395');
        $this->addSql('ALTER TABLE structure DROP CONSTRAINT FK_6F0137EA3FE997DE');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649D60322AC');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D6492534008B');
        $this->addSql('ALTER TABLE type DROP CONSTRAINT FK_8CDE57292534008B');
        $this->addSql('ALTER TABLE type_user DROP CONSTRAINT FK_5A9C1341C54C8C93');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649FE54D947');
        $this->addSql('ALTER TABLE structure DROP CONSTRAINT FK_6F0137EAFE54D947');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE timezone');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE structure');
        $this->addSql('DROP TABLE type');
        $this->addSql('DROP TABLE type_user');
        $this->addSql('DROP TABLE "group"');
        $this->addSql('DROP TABLE forget_token');
    }
}
