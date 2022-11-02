<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220921141808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE otherdoc (id UUID NOT NULL, file_id UUID NOT NULL, sitting_id UUID NOT NULL, name VARCHAR(512) NOT NULL, rank INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_798DA02D93CB796C ON otherdoc (file_id)');
        $this->addSql('CREATE INDEX IDX_798DA02D8014E66 ON otherdoc (sitting_id)');
        $this->addSql('COMMENT ON COLUMN otherdoc.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE otherdoc ADD CONSTRAINT FK_798DA02D93CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE otherdoc ADD CONSTRAINT FK_798DA02D8014E66 FOREIGN KEY (sitting_id) REFERENCES sitting (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE otherdoc');
    }
}
