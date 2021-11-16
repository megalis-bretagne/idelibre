<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Configuration;
use App\Entity\Structure;
use App\Repository\StructureRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211115210427 extends AbstractMigration implements ContainerAwareInterface
{

    private EntityManager $em;
    private StructureRepository $structureRepository;

    public function setContainer(ContainerInterface $container = null)
{
    $this->em = $container->get('doctrine.orm.entity_manager');
    $this->structureRepository = $this->em->getRepository(Structure::class);
}
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE configuration (id UUID NOT NULL, structure_id UUID DEFAULT NULL, is_shared_annotation BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A5E2A5D72534008B ON configuration (structure_id)');
        $this->addSql('ALTER TABLE configuration ADD CONSTRAINT FK_A5E2A5D72534008B FOREIGN KEY (structure_id) REFERENCES structure (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE data_controller_gdpr ALTER address TYPE VARCHAR(512)');
    }


    public function postUp(Schema $schema): void
    {
        $structures = $this->structureRepository->findAll();
        foreach ($structures as $structure) {
            $configuration = new Configuration();
            $configuration->setStructure($structure)
                ->setIsSharedAnnotation(true);
            $this->em->persist($configuration);
        }
        $this->em->flush();
    }


    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE configuration');
        $this->addSql('ALTER TABLE data_controller_gdpr ALTER address TYPE VARCHAR(255)');
    }
}
