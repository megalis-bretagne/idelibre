<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210103102115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE annex (id UUID NOT NULL, file_id UUID NOT NULL, project_id UUID NOT NULL, rank INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BD94A91693CB796C ON annex (file_id)');
        $this->addSql('CREATE INDEX IDX_BD94A916166D1F9C ON annex (project_id)');
        $this->addSql('CREATE TABLE connector (id UUID NOT NULL, structure_id UUID NOT NULL, name VARCHAR(255) NOT NULL, fields JSONB NOT NULL, dtype VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_148C456E2534008B ON connector (structure_id)');
        $this->addSql('CREATE TABLE convocation (id UUID NOT NULL, sitting_id UUID NOT NULL, actor_id UUID NOT NULL, sent_timestamp_id UUID DEFAULT NULL, received_timestamp_id UUID DEFAULT NULL, is_read BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_active BOOLEAN NOT NULL, is_emailed BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C03B3F5F8014E66 ON convocation (sitting_id)');
        $this->addSql('CREATE INDEX IDX_C03B3F5F10DAF24A ON convocation (actor_id)');
        $this->addSql('CREATE INDEX IDX_C03B3F5F9B585FBC ON convocation (sent_timestamp_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C03B3F5F420BD3C8 ON convocation (received_timestamp_id)');
        $this->addSql('CREATE TABLE email_template (id UUID NOT NULL, structure_id UUID NOT NULL, type_id UUID DEFAULT NULL, name VARCHAR(255) NOT NULL, content TEXT NOT NULL, subject VARCHAR(255) NOT NULL, is_default BOOLEAN NOT NULL, category VARCHAR(255) NOT NULL, is_attachment BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9C0600CA5E237E06 ON email_template (name)');
        $this->addSql('CREATE INDEX IDX_9C0600CA2534008B ON email_template (structure_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9C0600CAC54C8C93 ON email_template (type_id)');
        $this->addSql('CREATE TABLE file (id UUID NOT NULL, path VARCHAR(512) NOT NULL, size DOUBLE PRECISION DEFAULT NULL, name VARCHAR(125) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE forget_token (id UUID NOT NULL, user_id UUID NOT NULL, token VARCHAR(255) NOT NULL, expire_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_51C96252A76ED395 ON forget_token (user_id)');
        $this->addSql('CREATE TABLE gdpr (id UUID NOT NULL, company_name VARCHAR(255) NOT NULL, address VARCHAR(512) NOT NULL, representative VARCHAR(255) NOT NULL, quality VARCHAR(255) NOT NULL, siret VARCHAR(255) NOT NULL, ape VARCHAR(255) NOT NULL, company_phone VARCHAR(255) NOT NULL, company_email VARCHAR(255) NOT NULL, dpo_email VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "group" (id UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6DC044C55E237E06 ON "group" (name)');
        $this->addSql('CREATE TABLE party (id UUID NOT NULL, structure_id UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_89954EE05E237E06 ON party (name)');
        $this->addSql('CREATE INDEX IDX_89954EE02534008B ON party (structure_id)');
        $this->addSql('CREATE TABLE project (id UUID NOT NULL, file_id UUID NOT NULL, theme_id UUID DEFAULT NULL, reporter_id UUID DEFAULT NULL, sitting_id UUID NOT NULL, name VARCHAR(512) NOT NULL, rank INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2FB3D0EE93CB796C ON project (file_id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE59027487 ON project (theme_id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EEE1CFE6F5 ON project (reporter_id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE8014E66 ON project (sitting_id)');
        $this->addSql('COMMENT ON COLUMN project.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE role (id UUID NOT NULL, name VARCHAR(255) NOT NULL, composites JSONB NOT NULL, pretty_name VARCHAR(255) NOT NULL, is_in_structure_role BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_57698A6A5E237E06 ON role (name)');
        $this->addSql('CREATE TABLE sitting (id UUID NOT NULL, type_id UUID DEFAULT NULL, structure_id UUID NOT NULL, convocation_file_id UUID DEFAULT NULL, name VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, revision INT NOT NULL, is_archived BOOLEAN NOT NULL, place VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E8B678E3C54C8C93 ON sitting (type_id)');
        $this->addSql('CREATE INDEX IDX_E8B678E32534008B ON sitting (structure_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E8B678E3B206B375 ON sitting (convocation_file_id)');
        $this->addSql('CREATE UNIQUE INDEX IDX_SITTING_NAME_DATE_STRUCTURE ON sitting (name, structure_id, date)');
        $this->addSql('CREATE TABLE structure (id UUID NOT NULL, group_id UUID DEFAULT NULL, timezone_id UUID NOT NULL, name VARCHAR(255) NOT NULL, reply_to VARCHAR(255) NOT NULL, suffix VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6F0137EA5E237E06 ON structure (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6F0137EAB5B087DE ON structure (suffix)');
        $this->addSql('CREATE INDEX IDX_6F0137EAFE54D947 ON structure (group_id)');
        $this->addSql('CREATE INDEX IDX_6F0137EA3FE997DE ON structure (timezone_id)');
        $this->addSql('CREATE TABLE theme (id UUID NOT NULL, tree_root UUID DEFAULT NULL, parent_id UUID DEFAULT NULL, structure_id UUID NOT NULL, name VARCHAR(255) NOT NULL, lft INT NOT NULL, lvl INT NOT NULL, rgt INT NOT NULL, full_name VARCHAR(512) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9775E708A977936C ON theme (tree_root)');
        $this->addSql('CREATE INDEX IDX_9775E708727ACA70 ON theme (parent_id)');
        $this->addSql('CREATE INDEX IDX_9775E7082534008B ON theme (structure_id)');
        $this->addSql('CREATE INDEX lft_ix ON theme (lft)');
        $this->addSql('CREATE INDEX rgt_ix ON theme (rgt)');
        $this->addSql('CREATE INDEX lvl_ix ON theme (lvl)');
        $this->addSql('CREATE TABLE timestamp (id UUID NOT NULL, create_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, file_path_content VARCHAR(255) NOT NULL, file_path_tsa VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE timezone (id UUID NOT NULL, name VARCHAR(255) NOT NULL, info VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3701B2975E237E06 ON timezone (name)');
        $this->addSql('CREATE TABLE type (id UUID NOT NULL, structure_id UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8CDE57292534008B ON type (structure_id)');
        $this->addSql('CREATE UNIQUE INDEX IDX_TYPE_NAME_STRUCTURE ON type (name, structure_id)');
        $this->addSql('CREATE TABLE type_user (type_id UUID NOT NULL, user_id UUID NOT NULL, PRIMARY KEY(type_id, user_id))');
        $this->addSql('CREATE INDEX IDX_5A9C1341C54C8C93 ON type_user (type_id)');
        $this->addSql('CREATE INDEX IDX_5A9C1341A76ED395 ON type_user (user_id)');
        $this->addSql('CREATE TABLE type_secretary (type_id UUID NOT NULL, user_id UUID NOT NULL, PRIMARY KEY(type_id, user_id))');
        $this->addSql('CREATE INDEX IDX_371442CEC54C8C93 ON type_secretary (type_id)');
        $this->addSql('CREATE INDEX IDX_371442CEA76ED395 ON type_secretary (user_id)');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, structure_id UUID DEFAULT NULL, group_id UUID DEFAULT NULL, role_id UUID DEFAULT NULL, party_id UUID DEFAULT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, title VARCHAR(255) DEFAULT NULL, gender INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON "user" (username)');
        $this->addSql('CREATE INDEX IDX_8D93D6492534008B ON "user" (structure_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649FE54D947 ON "user" (group_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649D60322AC ON "user" (role_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649213C1059 ON "user" (party_id)');
        $this->addSql('ALTER TABLE annex ADD CONSTRAINT FK_BD94A91693CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE annex ADD CONSTRAINT FK_BD94A916166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE connector ADD CONSTRAINT FK_148C456E2534008B FOREIGN KEY (structure_id) REFERENCES structure (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT FK_C03B3F5F8014E66 FOREIGN KEY (sitting_id) REFERENCES sitting (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT FK_C03B3F5F10DAF24A FOREIGN KEY (actor_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT FK_C03B3F5F9B585FBC FOREIGN KEY (sent_timestamp_id) REFERENCES timestamp (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT FK_C03B3F5F420BD3C8 FOREIGN KEY (received_timestamp_id) REFERENCES timestamp (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE email_template ADD CONSTRAINT FK_9C0600CA2534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE email_template ADD CONSTRAINT FK_9C0600CAC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE forget_token ADD CONSTRAINT FK_51C96252A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE party ADD CONSTRAINT FK_89954EE02534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE93CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE59027487 FOREIGN KEY (theme_id) REFERENCES theme (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEE1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE8014E66 FOREIGN KEY (sitting_id) REFERENCES sitting (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sitting ADD CONSTRAINT FK_E8B678E3C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sitting ADD CONSTRAINT FK_E8B678E32534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sitting ADD CONSTRAINT FK_E8B678E3B206B375 FOREIGN KEY (convocation_file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EAFE54D947 FOREIGN KEY (group_id) REFERENCES "group" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EA3FE997DE FOREIGN KEY (timezone_id) REFERENCES timezone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E708A977936C FOREIGN KEY (tree_root) REFERENCES theme (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E708727ACA70 FOREIGN KEY (parent_id) REFERENCES theme (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E7082534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE type ADD CONSTRAINT FK_8CDE57292534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE type_user ADD CONSTRAINT FK_5A9C1341C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE type_user ADD CONSTRAINT FK_5A9C1341A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE type_secretary ADD CONSTRAINT FK_371442CEC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE type_secretary ADD CONSTRAINT FK_371442CEA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D6492534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649FE54D947 FOREIGN KEY (group_id) REFERENCES "group" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649213C1059 FOREIGN KEY (party_id) REFERENCES party (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE annex DROP CONSTRAINT FK_BD94A91693CB796C');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE93CB796C');
        $this->addSql('ALTER TABLE sitting DROP CONSTRAINT FK_E8B678E3B206B375');
        $this->addSql('ALTER TABLE structure DROP CONSTRAINT FK_6F0137EAFE54D947');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649FE54D947');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649213C1059');
        $this->addSql('ALTER TABLE annex DROP CONSTRAINT FK_BD94A916166D1F9C');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649D60322AC');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT FK_C03B3F5F8014E66');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE8014E66');
        $this->addSql('ALTER TABLE connector DROP CONSTRAINT FK_148C456E2534008B');
        $this->addSql('ALTER TABLE email_template DROP CONSTRAINT FK_9C0600CA2534008B');
        $this->addSql('ALTER TABLE party DROP CONSTRAINT FK_89954EE02534008B');
        $this->addSql('ALTER TABLE sitting DROP CONSTRAINT FK_E8B678E32534008B');
        $this->addSql('ALTER TABLE theme DROP CONSTRAINT FK_9775E7082534008B');
        $this->addSql('ALTER TABLE type DROP CONSTRAINT FK_8CDE57292534008B');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D6492534008B');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE59027487');
        $this->addSql('ALTER TABLE theme DROP CONSTRAINT FK_9775E708A977936C');
        $this->addSql('ALTER TABLE theme DROP CONSTRAINT FK_9775E708727ACA70');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT FK_C03B3F5F9B585FBC');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT FK_C03B3F5F420BD3C8');
        $this->addSql('ALTER TABLE structure DROP CONSTRAINT FK_6F0137EA3FE997DE');
        $this->addSql('ALTER TABLE email_template DROP CONSTRAINT FK_9C0600CAC54C8C93');
        $this->addSql('ALTER TABLE sitting DROP CONSTRAINT FK_E8B678E3C54C8C93');
        $this->addSql('ALTER TABLE type_user DROP CONSTRAINT FK_5A9C1341C54C8C93');
        $this->addSql('ALTER TABLE type_secretary DROP CONSTRAINT FK_371442CEC54C8C93');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT FK_C03B3F5F10DAF24A');
        $this->addSql('ALTER TABLE forget_token DROP CONSTRAINT FK_51C96252A76ED395');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EEE1CFE6F5');
        $this->addSql('ALTER TABLE type_user DROP CONSTRAINT FK_5A9C1341A76ED395');
        $this->addSql('ALTER TABLE type_secretary DROP CONSTRAINT FK_371442CEA76ED395');
        $this->addSql('DROP TABLE annex');
        $this->addSql('DROP TABLE connector');
        $this->addSql('DROP TABLE convocation');
        $this->addSql('DROP TABLE email_template');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE forget_token');
        $this->addSql('DROP TABLE gdpr');
        $this->addSql('DROP TABLE "group"');
        $this->addSql('DROP TABLE party');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE sitting');
        $this->addSql('DROP TABLE structure');
        $this->addSql('DROP TABLE theme');
        $this->addSql('DROP TABLE timestamp');
        $this->addSql('DROP TABLE timezone');
        $this->addSql('DROP TABLE type');
        $this->addSql('DROP TABLE type_user');
        $this->addSql('DROP TABLE type_secretary');
        $this->addSql('DROP TABLE "user"');
    }
}
