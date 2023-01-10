<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230109073759 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE convocation ADD is_remote BOOLEAN');
        $this->addSql('UPDATE convocation SET is_remote = false WHERE is_remote IS NULL');
        $this->addSql('ALTER TABLE convocation ALTER is_remote SET DEFAULT  \'false\'');
        $this->addSql('ALTER TABLE convocation ALTER is_remote SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE convocation DROP is_remote');
    }
}
