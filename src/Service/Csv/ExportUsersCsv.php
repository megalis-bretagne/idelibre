<?php

namespace App\Service\Csv;

use App\Entity\Group;
use App\Entity\Structure;
use App\Repository\UserRepository;
use App\Service\Export\ExportToZip;
use App\Service\Util\Sanitizer;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\UnavailableStream;
use League\Csv\Writer;
use Symfony\Component\Filesystem\Filesystem;

class ExportUsersCsv
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly Sanitizer $sanitizer,
        private readonly FileSystem $fileSystem,
    ) {
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

        $csvWriter = Writer::createFromPath( $pathDir . '/' . $this->sanitizer->fileNameSanitizer($structure->getName(), 255) . '.csv', 'w+');

        foreach ($users as $user) {
            $csvWriter->insertOne(
                [
                    $user->getGender(),
                    $this->formatUsername($user->getUsername()),
                    $user->getFirstName(),
                    $user->getLastName(),
                    $user->getEmail(),
                    $this->formatRole($user->getRole()->getPrettyName()),
                    $user->getPhone(),
                    $user->getTitle() ? $user->getTitle() : null,
                    $user->getDeputy() ? $this->formatUsername($user->getDeputy()->getUsername()) : null,
                ]
            );
        }
        return $pathDir . '/' . $this->sanitizer->fileNameSanitizer($structure->getName(), 255) . '.csv';
    }


    private function formatUsername(string $username): string
    {
        return substr($username, 0, strpos($username, "@"));
    }

    private function formatRole(string $role): int
    {
        return match ($role) {
            'Gestionnaire de séance' => 1,
            'Administrateur' => 2,
            'Elu' => 3,
            'Personnel administratif' => 4,
            'Invité' => 5,
            'Suppléant' => 6,
            default => 0,
        };
    }

    public function csvPath(): string
    {
        $pathFile = '/tmp';

        if (!$this->fileSystem->exists($pathFile)) {
            $this->fileSystem->mkdir($pathFile, 0755);
        }
        return $pathFile;
    }
}
