<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220921090856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE type ADD is_sms_guests BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE type ADD is_sms_employees BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE type RENAME COLUMN is_sms TO is_sms_actors');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE type ADD is_sms BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE type DROP is_sms_actors');
        $this->addSql('ALTER TABLE type DROP is_sms_guests');
        $this->addSql('ALTER TABLE type DROP is_sms_employees');
    }
}
