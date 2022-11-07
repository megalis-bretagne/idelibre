<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221104124606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE structure ADD is_active BOOLEAN DEFAULT \'true\' NOT NULL');
        $this->addSql('DROP SEQUENCE party_legacy_seq CASCADE');
        $this->addSql('ALTER TABLE structure ADD minimum_entropy INT');
        $this->addSql('UPDATE structure SET minimum_entropy = 80;');
        $this->addSql('ALTER TABLE structure ALTER COLUMN minimum_entropy SET NOT NULL;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE party_legacy_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE structure DROP minimum_entropy');
    }
}
