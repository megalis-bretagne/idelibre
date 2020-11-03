<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201102153559 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE convocation ADD sent_timestamp_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE convocation ADD received_timestamp_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT FK_C03B3F5F9B585FBC FOREIGN KEY (sent_timestamp_id) REFERENCES timestamp (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE convocation ADD CONSTRAINT FK_C03B3F5F420BD3C8 FOREIGN KEY (received_timestamp_id) REFERENCES timestamp (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C03B3F5F9B585FBC ON convocation (sent_timestamp_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C03B3F5F420BD3C8 ON convocation (received_timestamp_id)');
        $this->addSql('ALTER TABLE "timestamp" DROP CONSTRAINT fk_a5d6e63ee8746f65');
        $this->addSql('DROP INDEX uniq_a5d6e63ee8746f65');
        $this->addSql('ALTER TABLE "timestamp" DROP convocation_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE timestamp ADD convocation_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE timestamp ADD CONSTRAINT fk_a5d6e63ee8746f65 FOREIGN KEY (convocation_id) REFERENCES convocation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_a5d6e63ee8746f65 ON timestamp (convocation_id)');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT FK_C03B3F5F9B585FBC');
        $this->addSql('ALTER TABLE convocation DROP CONSTRAINT FK_C03B3F5F420BD3C8');
        $this->addSql('DROP INDEX UNIQ_C03B3F5F9B585FBC');
        $this->addSql('DROP INDEX UNIQ_C03B3F5F420BD3C8');
        $this->addSql('ALTER TABLE convocation DROP sent_timestamp_id');
        $this->addSql('ALTER TABLE convocation DROP received_timestamp_id');
    }
}
