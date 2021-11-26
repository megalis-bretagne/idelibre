<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function file_get_contents;

class MigrateFrom40Command extends Command
{

    protected static $defaultName = 'migrate:from40';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, string $name = null)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('update migration table');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if (!$this->isInit()) {
            $io->text('database is not initialized, nothing to update');

            return 0;
        }

        if ($this->alreadyExistDoctrineMigrationTable()) {
            $io->text('database is already updated');

            return 0;
        }


        $io->text('Beginning update');
        $pdo = $this->entityManager->getConnection()->getWrappedConnection();
        $sqlCreate = "
            create table doctrine_migration_versions(
                version character VARYING(191) PRIMARY KEY not null,
                executed_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NULL,
                execution_time integer              
            );
";

        $sqlCopyDataToNewMigrationTable = "INSERT INTO doctrine_migration_versions (version, executed_at, execution_time)
 SELECT concat('DoctrineMigrations\Version', version), executed_at, 1 FROM migration_versions;";;

        $pdo->beginTransaction();
        try {
            $pdo->exec($sqlCreate);
            $pdo->exec($sqlCopyDataToNewMigrationTable);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

        $io->success('update migration table done');
        return 0;


    }

    private function isInit()
    {
        $pdo = $this->entityManager->getConnection()->getWrappedConnection();
        $statement = $pdo->prepare('select * from "user"');
        $statement->execute();
        $count = $statement->rowCount();

        return $count > 0;
    }

    private function alreadyExistDoctrineMigrationTable():bool
    {
        $pdo = $this->entityManager->getConnection()->getWrappedConnection();

        try{
            $pdo->exec('select * from doctrine_migration_versions');
        }catch (Exception) {
            return false;
        }

        return true;
    }
}
