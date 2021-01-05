<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210104104220 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT fk_c03b3f5f10daf24a');
        $this->addSql('DROP INDEX idx_c03b3f5f10daf24a');
        $this->addSql('ALTER TABLE convocation ADD category VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE convocation RENAME COLUMN actor_id TO user_id');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT FK_C03B3F5FA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C03B3F5FA76ED395 ON convocation (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT FK_C03B3F5FA76ED395');
        $this->addSql('DROP INDEX IDX_C03B3F5FA76ED395');
        $this->addSql('ALTER TABLE convocation DROP category');
        $this->addSql('ALTER TABLE convocation RENAME COLUMN user_id TO actor_id');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT fk_c03b3f5f10daf24a FOREIGN KEY (actor_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_c03b3f5f10daf24a ON convocation (actor_id)');
    }
}
