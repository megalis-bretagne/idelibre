<?php

namespace App\Service\Csv;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;

class ExportUsersCsv
{

    private function runQuery(string $query)
    {
        $psqlCmd = 'psql --dbname=' . getenv('DATABASE_URL') . ' -c ' . '"' . $query . '"';

        exec($psqlCmd, $out, $resultCode);

        if (0 != $resultCode) {
            throw new CsvException('erreur dans le sql : ' . $query);
        }
    }

    public function exportUsers(string $structureId, string $pathDir): void
    {
        $path = $pathDir . '/user.csv';
        $query = '\copy (select * from ' . '\"user\"' . " where structure_id ='$structureId') to '$path' delimiter ',' csv HEADER ENCODING 'UTF8';";

        $this->runQuery($query);
    }

    public function execute($structureId): int
    {
        $fileSystem = new Filesystem();

        $pathDir = '/data/files/export/' . $structureId;

        $fileSystem->remove($pathDir);
        $fileSystem->mkdir($pathDir);


        $this->exportUsers($structureId, $pathDir);

        return Command::SUCCESS;
    }

}
