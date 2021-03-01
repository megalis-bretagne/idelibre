<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210103103534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX uniq_9c0600ca5e237e06');
        $this->addSql('CREATE UNIQUE INDEX IDX_EMAIL_NAME_STRUCTURE ON email_template (name, structure_id)');
        $this->addSql('DROP INDEX uniq_89954ee05e237e06');
        $this->addSql('CREATE UNIQUE INDEX IDX_PARTY_NAME_STRUCTURE ON party (name, structure_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX IDX_EMAIL_NAME_STRUCTURE');
        $this->addSql('CREATE UNIQUE INDEX uniq_9c0600ca5e237e06 ON email_template (name)');
        $this->addSql('DROP INDEX IDX_PARTY_NAME_STRUCTURE');
        $this->addSql('CREATE UNIQUE INDEX uniq_89954ee05e237e06 ON party (name)');
    }
}
