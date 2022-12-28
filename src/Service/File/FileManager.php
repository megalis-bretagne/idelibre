<?php

namespace App\Service\File;

use App\Entity\Annex;
use App\Entity\File;
use App\Entity\Project;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Service\S3\ObjectStorageException;
use App\Service\S3\S3Manager;
use App\Service\VirusScan\VirusScanInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManager
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly EntityManagerInterface $em,
        private readonly S3Manager $s3Manager,
        private readonly LoggerInterface $logger,
        private readonly ParameterBagInterface $bag,
        private readonly VirusScanInterface $scan,
    ) {
    }

    public function fileExist(string $path): bool
    {
        return $this->filesystem->exists($path);
    }

    public function downloadToS3(string $path)
    {
        $dirname = dirname($path);
        if (false === is_dir($dirname)) {
            $this->filesystem->mkdir($dirname);
        }

        $file = $this->s3Manager->getObject($path);

        if (!$fp = fopen($path,'w+')){
            dd( "Impossible d'ouvrir le fichier ($path)");
        }

        if (false === fwrite($fp, $file['Body'])){
            dd("Impossible d'écrire dans le fichier ($path)");
        }

        fclose($fp);
    }

    public function save(UploadedFile $uploadedFile, Structure $structure): ?File
    {
        if (false === $this->checkVirusFile($uploadedFile)) {
            dd('ERREUR VIRUS');
        }

        $file = new File();
        $file
            ->setName($uploadedFile->getClientOriginalName())
            ->setSize($uploadedFile->getSize())
        ;

        $fileName = $this->sanitizeAndUniqueFileName($uploadedFile);
        $savedFile = $uploadedFile->move($this->getAndCreateDestinationDirectory($structure), $fileName);

        $pathFile = ($savedFile->getRealPath());

        $file->setPath($pathFile);
        $this->em->persist($file);

        $this->transfertToS3($pathFile);

        return $file;
    }

    public function transfertToS3(string $path)
    {
        if (false === $this->fileExist($path)) {
            dd("ERROR");
        }

        try {
            $this->s3Manager->addObject(
                $path,
                $path
            );
        } catch (ObjectStorageException $e) {
            $this->logger->error($e);
            return false;
        }
    }

//    public function saveToS3(File $file, string $pathFile): bool
//    {
//        try {
//            $this->s3Manager->addObject(
//                $file->getPath(),
//                $pathFile
//            );
//        } catch (ObjectStorageException $e) {
//            $this->logger->error($e);
//            return false;
//        }
//
//        return true;
//    }

//    private function sendS3(Structure $structure, UploadedFile $uploadedFile)
//    {
//        if (false === $this->checkVirusFile($uploadedFile)) {
//            return false;
//        }
//
//        $fileName = $this->sanitizeAndUniqueFileName($uploadedFile);
//
//        $key = $this->bag->get('document_files_directory') . $structure->getId() . date('/Y/m/') . $fileName;
//
//        try {
//            $this->s3Manager->addObject(
//                $uploadedFile->getRealPath(),
//                $key
//            );
//        } catch (ObjectStorageException $e) {
//            $this->logger->error($e);
//            return false;
//        }
//
//        return $key;
//    }

    private function sanitizeAndUniqueFileName(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate(
            'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
            $originalFilename
        );

        return $safeFilename . '-' . uniqid() . $this->getExtension($file);
    }

    private function getAndCreateDestinationDirectory(Structure $structure): string
    {
        $directoryPath = $this->bag->get('document_files_directory') . $structure->getId() . '/' . date('y') . '/' . date('m');
        $this->filesystem->mkdir($directoryPath);

        return $directoryPath;
    }

    private function getExtension(UploadedFile $file): string
    {
        return $file->getClientOriginalExtension() ? '.' . $file->getClientOriginalExtension() : '';
    }

    public function delete(?File $file): void
    {
        if (!$file) {
            return;
        }
        $this->filesystem->remove($file->getPath());
        $this->em->remove($file);
    }

    public function replaceConvocationFile(UploadedFile $uploadedFile, Sitting $sitting): File
    {
        $this->delete($sitting->getConvocationFile());

        return $this->save($uploadedFile, $sitting->getStructure());
    }

    public function replaceInvitationFile(UploadedFile $uploadedFile, Sitting $sitting): File
    {
        $this->delete($sitting->getInvitationFile());

        return $this->save($uploadedFile, $sitting->getStructure());
    }

    /**
     * @return file[]
     */
    public function listFilesFromSitting(Sitting $sitting): array
    {
        $files = [];
        $files[] = $sitting->getConvocationFile();

        return [...$files, ...$this->addProjectsAndAnnexes($sitting->getProjects())];
    }

    /**
     * @param iterable<Project> $projects
     *
     * @return File[]
     */
    private function addProjectsAndAnnexes(iterable $projects): array
    {
        $files = [];
        foreach ($projects as $project) {
            $files[] = $project->getFile();
            $files = [...$files, ...$this->addAnnexes($project->getAnnexes())];
        }

        return $files;
    }

    /**
     * @param iterable<Annex> $annexes
     *
     * @return File[]
     */
    private function addAnnexes(iterable $annexes): array
    {
        $files = [];
        foreach ($annexes as $annex) {
            $files[] = $annex->getFile();
        }

        return $files;
    }

    private function checkVirusFile(UploadedFile $file): bool
    {
        if (!$this->scan->isFileSafe($file->getRealPath())) {
            $this->logger->error('error while scanning file');

            return false;
        }

        return true;
    }
}
