<?php

namespace App\Command;

use App\Entity\GeneratedFile;
use App\Entity\Sitting;
use App\Repository\FileRepository;
use App\Repository\SittingRepository;
use App\Service\File\FileManager;
use App\Service\GeneratedFile\GeneratedFileManager;
use Doctrine\ORM\EntityManagerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'generated:Pdf_and_Zip')]
class AddPathPdfAndZipGeneratedToBdd extends Command
{
    public function __construct(
        private readonly ParameterBagInterface $bag,
        private readonly SittingRepository $sittingRepository,
        private readonly GeneratedFileManager $generatedFileManager,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generated paths pdf and zip in Bdd')
            ->setHelp('Get path and save in Bdd');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $directorysCheck = [
            $this->bag->get('document_zip_directory'),
            $this->bag->get('document_full_pdf_directory')
        ];

        foreach ($directorysCheck as $directoryCheck) {
            $pathFiles = $this->getDirContents($directoryCheck);

            foreach ($pathFiles as $pathFile) {
                $infoFile = pathinfo($pathFile);

                /** @var Sitting $sitting */
                $sitting = $this->sittingRepository->findOneBy(['id' => $infoFile['filename']]);

                if (in_array($infoFile['extension'], [GeneratedFile::ZIP, GeneratedFile::PDF])) {
                    $this->generatedFileManager->addOrReplace(
                        $infoFile['extension'],
                        $sitting,
                        $pathFile
                    );

                    $io->success($pathFile . ' ajouté à la séance : ' . $sitting->getName() . '( ' . $sitting->getId() . ' )');
                } else {
                    $io->error($pathFile);
                }
            }
        }

        return Command::SUCCESS;
    }

    private function getDirContents($dir): array
    {
        $fileList = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

        foreach ($iterator as $file) {
            if ($file->isDir()) continue;
            $path = $file->getPathname();

            $fileList[] = $path;
        }

        return $fileList;
    }
}