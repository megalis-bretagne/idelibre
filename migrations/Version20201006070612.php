<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201006070612 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE theme (id UUID NOT NULL, tree_root UUID DEFAULT NULL, parent_id UUID DEFAULT NULL, name VARCHAR(255) NOT NULL, lft INT NOT NULL, lvl INT NOT NULL, rgt INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9775E708A977936C ON theme (tree_root)');
        $this->addSql('CREATE INDEX IDX_9775E708727ACA70 ON theme (parent_id)');
        $this->addSql('CREATE INDEX lft_ix ON theme (lft)');
        $this->addSql('CREATE INDEX rgt_ix ON theme (rgt)');
        $this->addSql('CREATE INDEX lvl_ix ON theme (lvl)');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E708A977936C FOREIGN KEY (tree_root) REFERENCES theme (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E708727ACA70 FOREIGN KEY (parent_id) REFERENCES theme (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE theme DROP CONSTRAINT FK_9775E708A977936C');
        $this->addSql('ALTER TABLE theme DROP CONSTRAINT FK_9775E708727ACA70');
        $this->addSql('DROP TABLE theme');
    }
}
