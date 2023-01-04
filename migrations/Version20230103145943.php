<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230103145943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "subscription" (id UUID NOT NULL, user_id UUID NOT NULL, accept_mail_recap BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3C664D3A76ED395 ON "subscription" (user_id)');
        $this->addSql('COMMENT ON COLUMN "subscription".id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "subscription".user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "subscription".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE "subscription" ADD CONSTRAINT FK_A3C664D3A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" DROP accept_mail_recap');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "subscription" DROP CONSTRAINT FK_A3C664D3A76ED395');
        $this->addSql('DROP TABLE "subscription"');
        $this->addSql('ALTER TABLE "user" ADD accept_mail_recap BOOLEAN NOT NULL');
    }
}
