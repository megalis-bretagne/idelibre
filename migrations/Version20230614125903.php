<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230614125903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD mandator_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD is_deputy BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('COMMENT ON COLUMN "user".mandator_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649F0C46DC5 FOREIGN KEY (mandator_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F0C46DC5 ON "user" (mandator_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649F0C46DC5');
        $this->addSql('DROP INDEX UNIQ_8D93D649F0C46DC5');
        $this->addSql('ALTER TABLE "user" DROP mandator_id');
        $this->addSql('ALTER TABLE "user" DROP is_deputy');
    }
}
