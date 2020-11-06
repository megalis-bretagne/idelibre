<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201016203753 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE project (id UUID NOT NULL, structure_id UUID NOT NULL, file_id UUID NOT NULL, theme_id UUID DEFAULT NULL, reporter_id UUID DEFAULT NULL, name VARCHAR(512) NOT NULL, rank INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE2534008B ON project (structure_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2FB3D0EE93CB796C ON project (file_id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE59027487 ON project (theme_id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EEE1CFE6F5 ON project (reporter_id)');
        $this->addSql('COMMENT ON COLUMN project.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE2534008B FOREIGN KEY (structure_id) REFERENCES structure (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE93CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE59027487 FOREIGN KEY (theme_id) REFERENCES theme (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEE1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX IDX_SITTING_NAME_DATE_STRUCTURE ON sitting (name, structure_id, date)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP INDEX IDX_SITTING_NAME_DATE_STRUCTURE');
    }
}
