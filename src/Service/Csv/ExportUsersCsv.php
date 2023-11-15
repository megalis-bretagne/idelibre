<?php

namespace App\Service\Csv;


use App\Entity\Group;
use App\Entity\Structure;
use App\Repository\GroupRepository;
use App\Repository\StructureRepository;
use App\Repository\UserRepository;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\UnavailableStream;
use League\Csv\Writer;
use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;

class ExportUsersCsv
{

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly FileSystem $fileSystem,
    )
    {
    }



    /**
     * @throws UnavailableStream
     * @throws CannotInsertRecord
     * @throws Exception
     */
    public function exportStructureUsers(Structure $structure): string
    {

        $users = $this->userRepository->findByStructure($structure)->getQuery()->getResult();

        $pathDir = $this->csvPath();

        $csvWriter = Writer::createFromPath($pathDir . '/' . $structure->getName() . '.csv', 'w+');

        $csvWriter->insertOne($this->getHeaders());

        foreach ($users as $user) {

            $csvWriter->insertOne(
                [
                    $user->getId(),
                    $user->getFirstName(),
                    $user->getLastName(),
                    $user->getIsActive() ? 'oui' : 'non',
                    $user->getRole()->getPrettyName(),
                    $user->getParty()?->getName() ? $user->getParty()->getName() : '',
                    ]
            );
        }


        return $pathDir . '/' .$structure->getName()  . '.csv';
    }

    private function getHeaders(): array
    {
        return ['id', 'Prénom', 'Nom', 'est actif' , 'Profil', 'Groupe_Politique'];
    }

    public function exportGroupUsers(Group $group): string
    {
        $structuresPath = [];
        foreach ($group->getStructures() as $structure) {
            $structuresPath[] = $this->exportStructureUsers($structure);
        }

        return $this->genZipAndGetPath($structuresPath);
    }

    private function genZipAndGetPath(array $structuresPath): string
    {
        $zip = new ZipArchive();
        $zipPath = '/tmp/' . uniqid('zip_report') . '.zip';
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($structuresPath as $structurePath) {
            $zip->addFile($structurePath);
        }

        $zip->close();

        foreach ($structuresPath as $structurePath) {
            $this->fileSystem->remove($structurePath);
        }

        return $zipPath;
    }



    public function csvPath(): string
    {
        $pathFile = '/tmp/export';

        if (!$this->fileSystem->exists($pathFile)){
            $this->fileSystem->mkdir($pathFile, 0755);
        }
        return $pathFile;
    }

}
