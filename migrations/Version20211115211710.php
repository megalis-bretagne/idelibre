<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211115211710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql("insert into api_role (id, name, composites, pretty_name) values ('ba98ff85-a720-4ca6-a877-c91de3ac4cf4', 'ApiStructureAdmin', '[\"ROLE_API_STRUCTURE_ADMIN\"]', 'Administrateur api')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE configuration DROP CONSTRAINT fk_a5e2a5d72534008b');
        $this->addSql('ALTER TABLE configuration ALTER structure_id DROP NOT NULL');
        $this->addSql('ALTER TABLE configuration ADD CONSTRAINT fk_a5e2a5d72534008b FOREIGN KEY (structure_id) REFERENCES structure (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
