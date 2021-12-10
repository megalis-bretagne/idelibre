<?php

namespace App;

use Doctrine\Common\DataFixtures\Executor\AbstractExecutor;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Liip\TestFixturesBundle\Services\DatabaseBackup\AbstractDatabaseBackup;
use Liip\TestFixturesBundle\Services\DatabaseBackup\DatabaseBackupInterface;

class PostgresqlDatabaseBackup extends AbstractDatabaseBackup implements DatabaseBackupInterface
{

    protected static $metadata;

    protected static $schemaUpdatedFlag = false;

    public function getBackupFilePath(): string
    {
        return $this->container->getParameter('kernel.cache_dir') . '/test_postgresql_' . md5(serialize($this->metadatas) . serialize($this->classNames)) . '.sql';
    }

    public function getReferenceBackupFilePath(): string
    {
        return $this->getBackupFilePath() . '.ser';
    }

    public function isBackupActual(): bool
    {
        return
            file_exists($this->getBackupFilePath())
            && file_exists($this->getReferenceBackupFilePath())
            && $this->isBackupUpToDate($this->getBackupFilePath());
    }

    public function backup(AbstractExecutor $executor): void
    {

        /** @var EntityManager $em */
        $em = $executor->getReferenceRepository()->getManager();
        $connection = $em->getConnection();

        $params = $connection->getParams();
        if (isset($params['master'])) {
            $params = $params['master'];
        }

        $dbName = $params['dbname'] ?? '';
        $dbHost = $params['host'] ?? '';

        $port = $params['port'] ?? '';

        $dbUser = $params['user'] ?? '';
        $dbPass = $params['password'] ?? '';

        $executor->getReferenceRepository()->save($this->getBackupFilePath());
        self::$metadata = $em->getMetadataFactory()->getLoadedMetadata();

        $pgDumpFn = "pg_dump --dbname=postgresql://{$dbUser}:{$dbPass}@{$dbHost}:{$port}/{$dbName} --insert --data-only --file {$this->getBackupFilePath()}  &>/dev/null";

        // dd($pgDumpFn);
        exec($pgDumpFn);
        $this->removeSearchPath($this->getBackupFilePath());
    }


    private function removeSearchPath(string $filePath)
    {
        $lineToReplace = "SELECT pg_catalog.set_config('search_path', '', false);";
        $contents = file_get_contents($filePath);
        $contents = str_replace($lineToReplace, '', $contents);
        file_put_contents($filePath, $contents);
    }

    public function restore(AbstractExecutor $executor, array $excludedTables = []): void
    {
        /** @var EntityManager $em */
        $em = $executor->getReferenceRepository()->getManager();
        $connection = $em->getConnection();
        // dd($connection->getSchemaManager()->listTableNames());


        $this->updateSchemaIfNeed($em);


        $truncateSql = [];
        foreach ($connection->getSchemaManager()->listTableNames() as $tableName) {
            if ($tableName === "abstract_closure") {
                continue;
            }

            if (!\in_array($tableName, $excludedTables, true)) {
                $truncateSql[] = 'DELETE FROM ' . $tableName ;
            }
        }



        $connection->executeStatement('SET session_replication_role = replica;');
        if (!empty($truncateSql)) {
            $connection->executeStatement(implode(';', $truncateSql));

        }

        $backup = $this->getBackup();


        if (!empty($backup)) {
            $connection->executeStatement($backup);
        }

        $connection->executeStatement('SET session_replication_role = DEFAULT;');
/*
        if (self::$metadata) {
            // it need for better performance
            foreach (self::$metadata as $class => $data) {
                $em->getMetadataFactory()->setMetadataFor($class, $data);
            }
            $executor->getReferenceRepository()->unserialize($this->getReferenceBackup());
        } else {
            $executor->getReferenceRepository()->unserialize($this->getReferenceBackup());
            self::$metadata = $em->getMetadataFactory()->getLoadedMetadata();
        } */
    }


    protected function getBackup()
    {
        return file_get_contents($this->getBackupFilePath());
    }

    protected function getReferenceBackup(): string
    {
        return file_get_contents($this->getReferenceBackupFilePath());
    }


    protected function updateSchemaIfNeed(EntityManager $em): void
    {
        if (!self::$schemaUpdatedFlag) {
            $schemaTool = new SchemaTool($em);
            $schemaTool->dropDatabase();
            if (!empty($this->metadatas)) {
                $schemaTool->createSchema($this->metadatas);
            }

            self::$schemaUpdatedFlag = true;
        }
    }


}
