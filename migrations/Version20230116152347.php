<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230116152347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attendance_token (id UUID NOT NULL, convocation_id UUID NOT NULL, token VARCHAR(255) NOT NULL, expired_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7F60FA55E8746F65 ON attendance_token (convocation_id)');
        $this->addSql('COMMENT ON COLUMN attendance_token.convocation_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE attendance_token ADD CONSTRAINT FK_7F60FA55E8746F65 FOREIGN KEY (convocation_id) REFERENCES convocation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE attendance_token DROP CONSTRAINT FK_7F60FA55E8746F65');
        $this->addSql('DROP TABLE attendance_token');
    }
}
