<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230209151604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attendance_token DROP CONSTRAINT FK_7F60FA55E8746F65');
        $this->addSql('ALTER TABLE attendance_token ADD CONSTRAINT FK_7F60FA55E8746F65 FOREIGN KEY (convocation_id) REFERENCES convocation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attendance_token DROP CONSTRAINT fk_7f60fa55e8746f65');
        $this->addSql('ALTER TABLE attendance_token ADD CONSTRAINT fk_7f60fa55e8746f65 FOREIGN KEY (convocation_id) REFERENCES convocation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
