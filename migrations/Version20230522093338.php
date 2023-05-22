<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230522093338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lsvote_sitting ADD sitting_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN lsvote_sitting.sitting_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE lsvote_sitting ADD CONSTRAINT FK_687C969D8014E66 FOREIGN KEY (sitting_id) REFERENCES sitting (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_687C969D8014E66 ON lsvote_sitting (sitting_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE lsvote_sitting DROP CONSTRAINT FK_687C969D8014E66');
        $this->addSql('DROP INDEX UNIQ_687C969D8014E66');
        $this->addSql('ALTER TABLE lsvote_sitting DROP sitting_id');
    }
}
