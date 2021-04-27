<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210427074722 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE annotation_user DROP CONSTRAINT FK_8E2206CDE075FC54');
        $this->addSql('ALTER TABLE annotation_user DROP CONSTRAINT FK_8E2206CDA76ED395');
        $this->addSql('ALTER TABLE annotation_user ADD CONSTRAINT FK_8E2206CDE075FC54 FOREIGN KEY (annotation_id) REFERENCES annotation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE annotation_user ADD CONSTRAINT FK_8E2206CDA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE annotation_user DROP CONSTRAINT fk_8e2206cde075fc54');
        $this->addSql('ALTER TABLE annotation_user DROP CONSTRAINT fk_8e2206cda76ed395');
        $this->addSql('ALTER TABLE annotation_user ADD CONSTRAINT fk_8e2206cde075fc54 FOREIGN KEY (annotation_id) REFERENCES annotation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE annotation_user ADD CONSTRAINT fk_8e2206cda76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
