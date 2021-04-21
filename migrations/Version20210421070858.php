<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210421070858 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE annotation (id UUID NOT NULL, author_id UUID NOT NULL, project_id UUID DEFAULT NULL, annex_id UUID DEFAULT NULL, sitting_id UUID DEFAULT NULL, page INT DEFAULT NULL, text TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, rect VARCHAR(1000) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2E443EF2F675F31B ON annotation (author_id)');
        $this->addSql('CREATE INDEX IDX_2E443EF2166D1F9C ON annotation (project_id)');
        $this->addSql('CREATE INDEX IDX_2E443EF2F64D4AB2 ON annotation (annex_id)');
        $this->addSql('CREATE INDEX IDX_2E443EF28014E66 ON annotation (sitting_id)');
        $this->addSql('CREATE TABLE annotation_user (annotation_id UUID NOT NULL, user_id UUID NOT NULL, PRIMARY KEY(annotation_id, user_id))');
        $this->addSql('CREATE INDEX IDX_8E2206CDE075FC54 ON annotation_user (annotation_id)');
        $this->addSql('CREATE INDEX IDX_8E2206CDA76ED395 ON annotation_user (user_id)');
        $this->addSql('ALTER TABLE annotation ADD CONSTRAINT FK_2E443EF2F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE annotation ADD CONSTRAINT FK_2E443EF2166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE annotation ADD CONSTRAINT FK_2E443EF2F64D4AB2 FOREIGN KEY (annex_id) REFERENCES annex (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE annotation ADD CONSTRAINT FK_2E443EF28014E66 FOREIGN KEY (sitting_id) REFERENCES sitting (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE annotation_user ADD CONSTRAINT FK_8E2206CDE075FC54 FOREIGN KEY (annotation_id) REFERENCES annotation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE annotation_user ADD CONSTRAINT FK_8E2206CDA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE email_template ALTER format DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" ALTER is_active DROP DEFAULT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE annotation_user DROP CONSTRAINT FK_8E2206CDE075FC54');
        $this->addSql('DROP TABLE annotation');
        $this->addSql('DROP TABLE annotation_user');
        $this->addSql('ALTER TABLE email_template ALTER format SET DEFAULT \'html\'');
        $this->addSql('ALTER TABLE "user" ALTER is_active SET DEFAULT \'true\'');
    }
}
