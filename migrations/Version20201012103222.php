<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201012103222 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE sitting ADD file_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE sitting ADD CONSTRAINT FK_E8B678E393CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E8B678E393CB796C ON sitting (file_id)');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT fk_c03b3f5f93cb796c');
        $this->addSql('DROP INDEX idx_c03b3f5f93cb796c');
        $this->addSql('ALTER TABLE convocation DROP file_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE sitting DROP CONSTRAINT FK_E8B678E393CB796C');
        $this->addSql('DROP INDEX UNIQ_E8B678E393CB796C');
        $this->addSql('ALTER TABLE sitting DROP file_id');
        $this->addSql('ALTER TABLE convocation ADD file_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT fk_c03b3f5f93cb796c FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_c03b3f5f93cb796c ON convocation (file_id)');
    }
}
