<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201011130249 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE file (id UUID NOT NULL, path VARCHAR(512) NOT NULL, size DOUBLE PRECISION DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE sitting ADD type_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE sitting ADD CONSTRAINT FK_E8B678E3C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E8B678E3C54C8C93 ON sitting (type_id)');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT FK_C03B3F5F8014E66');
        $this->addSql('ALTER TABLE convocation ADD file_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT FK_C03B3F5F93CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT FK_C03B3F5F8014E66 FOREIGN KEY (sitting_id) REFERENCES sitting (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C03B3F5F93CB796C ON convocation (file_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT FK_C03B3F5F93CB796C');
        $this->addSql('DROP TABLE file');
        $this->addSql('ALTER TABLE sitting DROP CONSTRAINT FK_E8B678E3C54C8C93');
        $this->addSql('DROP INDEX IDX_E8B678E3C54C8C93');
        $this->addSql('ALTER TABLE sitting DROP type_id');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT fk_c03b3f5f8014e66');
        $this->addSql('DROP INDEX IDX_C03B3F5F93CB796C');
        $this->addSql('ALTER TABLE convocation DROP file_id');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT fk_c03b3f5f8014e66 FOREIGN KEY (sitting_id) REFERENCES sitting (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
