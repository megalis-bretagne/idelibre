<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210928083821 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE calendar (id UUID NOT NULL, sitting_id UUID NOT NULL, duration INT NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6EA9A1468014E66 ON calendar (sitting_id)');
        $this->addSql('ALTER TABLE calendar ADD CONSTRAINT FK_6EA9A1468014E66 FOREIGN KEY (sitting_id) REFERENCES sitting (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE annotation DROP CONSTRAINT FK_2E443EF2F675F31B');
        $this->addSql('ALTER TABLE annotation ADD CONSTRAINT FK_2E443EF2F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "group" ALTER is_structure_creator DROP DEFAULT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE calendar');
        $this->addSql('ALTER TABLE "group" ALTER is_structure_creator SET DEFAULT \'false\'');
        $this->addSql('ALTER TABLE annotation DROP CONSTRAINT fk_2e443ef2f675f31b');
        $this->addSql('ALTER TABLE annotation ADD CONSTRAINT fk_2e443ef2f675f31b FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
