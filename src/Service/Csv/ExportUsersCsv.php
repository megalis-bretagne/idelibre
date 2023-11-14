<?php

namespace App\Service\Csv;


use App\Repository\GroupRepository;
use App\Repository\StructureRepository;
use Symfony\Component\Filesystem\Filesystem;

class ExportUsersCsv
{

    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly StructureRepository $structureRepository,
    )
    {
    }

    /**
     * @throws CsvException
     */
    public function generate($id): string
    {
        $structure = $this->structureRepository->find($id);
        $group = $this->groupRepository->find($id);
        if($structure){
            $this->exportUsers($id);
            return $this->csvPath($id) . '/user.csv';
        }
        if($group){
            $this->exportUsersFromGroup($id);
            return $this->csvPath($id) . '/user.csv';
        }
        throw new CsvException('Aucune information trouvée à exporter');
    }

    private function exportUsers(string $structureId): void
    {
        $pathDir = $this->csvPath($structureId);
        $path = $pathDir . '/user.csv';
        $query = '\copy (SELECT * FROM ' . '\"user\"' . " WHERE structure_id ='$structureId') to '$path' delimiter ',' csv HEADER ENCODING 'UTF8';";
        $psqlCmd = 'psql --dbname=' . getenv('DATABASE_URL') . ' -c ' . '"' . $query . '"';

        exec($psqlCmd, $out, $resultCode);

        if (0 != $resultCode) {
            throw new CsvException('erreur dans le sql : ' . $query);
        }
    }

    private function exportUsersFromGroup(string $groupId): void
    {
        $pathDir = $this->csvPath($groupId);
        $path = $pathDir . '/user.csv';
        #  $query = '\copy (SELECT * FROM ' . '\"user\"' . " WHERE group_id ='$groupId') to '$path' delimiter ',' csv HEADER ENCODING 'UTF8';";
        #$psqlCmd = 'psql --dbname=' . getenv('DATABASE_URL') . ' -c ' . '"' . $query . '"';

       # exec($psqlCmd, $out, $resultCode);

//        if (0 != $resultCode) {
//            throw new CsvException('erreur dans le sql : ' . $query);
//        }
    }



    public function csvPath($id): string
    {
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists('/data/files/files/export/' . $id)){
            $fileSystem->mkdir('/data/files/files/export/' . $id);
        }
        return '/data/files/files/export/' . $id;
    }

}
