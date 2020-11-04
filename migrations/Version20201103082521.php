<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201103082521 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT FK_C03B3F5F9B585FBC');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT FK_C03B3F5F420BD3C8');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT FK_C03B3F5F9B585FBC FOREIGN KEY (sent_timestamp_id) REFERENCES timestamp (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT FK_C03B3F5F420BD3C8 FOREIGN KEY (received_timestamp_id) REFERENCES timestamp (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT fk_c03b3f5f9b585fbc');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT fk_c03b3f5f420bd3c8');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT fk_c03b3f5f9b585fbc FOREIGN KEY (sent_timestamp_id) REFERENCES "timestamp" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT fk_c03b3f5f420bd3c8 FOREIGN KEY (received_timestamp_id) REFERENCES "timestamp" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
