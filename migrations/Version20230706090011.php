<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230706090011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD associated_with_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD attendance_option VARCHAR(255) DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN "user".associated_with_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649EBD115CC FOREIGN KEY (associated_with_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649EBD115CC ON "user" (associated_with_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649EBD115CC');
        $this->addSql('DROP INDEX UNIQ_8D93D649EBD115CC');
        $this->addSql('ALTER TABLE "user" DROP associated_with_id');
        $this->addSql('ALTER TABLE "user" DROP attendance_option');
    }
}
