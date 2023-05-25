<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230523133919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lsvote_sitting (id UUID NOT NULL, sitting_id UUID DEFAULT NULL, lsvote_sitting_id VARCHAR(255) NOT NULL, results JSONB NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_687C969D8014E66 ON lsvote_sitting (sitting_id)');
        $this->addSql('COMMENT ON COLUMN lsvote_sitting.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN lsvote_sitting.sitting_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN lsvote_sitting.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE lsvote_sitting ADD CONSTRAINT FK_687C969D8014E66 FOREIGN KEY (sitting_id) REFERENCES sitting (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE lsvote_sitting DROP CONSTRAINT FK_687C969D8014E66');
        $this->addSql('DROP TABLE lsvote_sitting');
    }
}
