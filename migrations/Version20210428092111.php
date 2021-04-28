<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210428092111 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE annotation DROP CONSTRAINT FK_2E443EF2166D1F9C');
        $this->addSql('ALTER TABLE annotation DROP CONSTRAINT FK_2E443EF2F64D4AB2');
        $this->addSql('ALTER TABLE annotation DROP CONSTRAINT FK_2E443EF28014E66');
        $this->addSql('ALTER TABLE annotation ADD CONSTRAINT FK_2E443EF2166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE annotation ADD CONSTRAINT FK_2E443EF2F64D4AB2 FOREIGN KEY (annex_id) REFERENCES annex (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE annotation ADD CONSTRAINT FK_2E443EF28014E66 FOREIGN KEY (sitting_id) REFERENCES sitting (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE annotation DROP CONSTRAINT fk_2e443ef2166d1f9c');
        $this->addSql('ALTER TABLE annotation DROP CONSTRAINT fk_2e443ef2f64d4ab2');
        $this->addSql('ALTER TABLE annotation DROP CONSTRAINT fk_2e443ef28014e66');
        $this->addSql('ALTER TABLE annotation ADD CONSTRAINT fk_2e443ef2166d1f9c FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE annotation ADD CONSTRAINT fk_2e443ef2f64d4ab2 FOREIGN KEY (annex_id) REFERENCES annex (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE annotation ADD CONSTRAINT fk_2e443ef28014e66 FOREIGN KEY (sitting_id) REFERENCES sitting (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
