<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201029160002 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE annex (id UUID NOT NULL, file_id UUID NOT NULL, project_id UUID NOT NULL, rank INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BD94A91693CB796C ON annex (file_id)');
        $this->addSql('CREATE INDEX IDX_BD94A916166D1F9C ON annex (project_id)');
        $this->addSql('ALTER TABLE annex ADD CONSTRAINT FK_BD94A91693CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE annex ADD CONSTRAINT FK_BD94A916166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT fk_2fb3d0ee2534008b');
        $this->addSql('DROP INDEX idx_2fb3d0ee2534008b');
        $this->addSql('ALTER TABLE project RENAME COLUMN structure_id TO sitting_id');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE8014E66 FOREIGN KEY (sitting_id) REFERENCES sitting (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE8014E66 ON project (sitting_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE annex');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE8014E66');
        $this->addSql('DROP INDEX IDX_2FB3D0EE8014E66');
        $this->addSql('ALTER TABLE project RENAME COLUMN sitting_id TO structure_id');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT fk_2fb3d0ee2534008b FOREIGN KEY (structure_id) REFERENCES structure (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_2fb3d0ee2534008b ON project (structure_id)');
    }
}
