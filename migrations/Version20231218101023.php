<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231218101023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE party_legacy_seq CASCADE');
        $this->addSql('ALTER TABLE annex ADD title VARCHAR(512) DEFAULT NULL');
        $this->addSql('ALTER TABLE convocation ALTER attendance SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE "user" DROP attendance_option');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE party_legacy_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE "user" ADD attendance_option VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE annex DROP title');
        $this->addSql('ALTER TABLE convocation ALTER attendance DROP DEFAULT');
    }
}
