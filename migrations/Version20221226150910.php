<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221226150910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE configuration ALTER sitting_suppression_delay SET DEFAULT \'10000 years\'');
        $this->addSql('update configuration set sitting_suppression_delay = \'10000 years\' where sitting_suppression_delay is null');
        $this->addSql('ALTER TABLE configuration ALTER sitting_suppression_delay SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE configuration ALTER sitting_suppression_delay DROP DEFAULT');
        $this->addSql('ALTER TABLE configuration ALTER sitting_suppression_delay DROP NOT NULL');
    }
}
