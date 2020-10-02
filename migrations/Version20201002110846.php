<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201002110846 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_3701B2975E237E06 ON timezone (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6F0137EAB5B087DE ON structure (suffix)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8CDE57295E237E06 ON type (name)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_3701B2975E237E06');
        $this->addSql('DROP INDEX UNIQ_6F0137EAB5B087DE');
        $this->addSql('DROP INDEX UNIQ_8CDE57295E237E06');
    }
}
