<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230120092152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE structure ADD can_edit_reply_to BOOLEAN');
        $this->addSql('UPDATE structure SET can_edit_reply_to = true WHERE can_edit_reply_to IS NULL');
        $this->addSql('ALTER TABLE structure ALTER can_edit_reply_to SET DEFAULT  \'true\'');
        $this->addSql('ALTER TABLE structure ALTER can_edit_reply_to SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE structure DROP can_edit_reply_to');
    }
}
