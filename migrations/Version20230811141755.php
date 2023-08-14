<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230811141755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE convocation ADD deputy_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN convocation.deputy_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT FK_C03B3F5F4B6F93BB FOREIGN KEY (deputy_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C03B3F5F4B6F93BB ON convocation (deputy_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT FK_C03B3F5F4B6F93BB');
        $this->addSql('DROP INDEX UNIQ_C03B3F5F4B6F93BB');
        $this->addSql('ALTER TABLE convocation DROP deputy_id');
    }
}
