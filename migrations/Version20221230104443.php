<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221230104443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE generated_file (id UUID NOT NULL, sitting_id UUID NOT NULL, file_id UUID NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75A703F8014E66 ON generated_file (sitting_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_75A703F93CB796C ON generated_file (file_id)');
        $this->addSql('COMMENT ON COLUMN generated_file.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN generated_file.sitting_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN generated_file.file_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE generated_file ADD CONSTRAINT FK_75A703F8014E66 FOREIGN KEY (sitting_id) REFERENCES sitting (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE generated_file ADD CONSTRAINT FK_75A703F93CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE generated_file DROP CONSTRAINT FK_75A703F8014E66');
        $this->addSql('ALTER TABLE generated_file DROP CONSTRAINT FK_75A703F93CB796C');
        $this->addSql('DROP TABLE generated_file');
    }
}
