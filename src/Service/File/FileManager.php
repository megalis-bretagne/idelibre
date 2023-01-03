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
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

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

    public function downloadToS3(string $path): bool
    {
        $dirname = dirname($path);
        if (false === is_dir($dirname)) {
            $this->filesystem->mkdir($dirname);
        }

        $file = $this->s3Manager->getObject($path);

        if (!$fp = fopen($path, 'w+')) {
            return false;
        }

        if (false === fwrite($fp, $file['Body'])) {
            fclose($fp);
            return false;
        }

        fclose($fp);

        return true;
    }

    public function save(UploadedFile $uploadedFile, Structure $structure): ?File
    {
        if (false === $this->checkVirusFile($uploadedFile)) {
            throw new BadRequestException('VIRUS');
        }

        $file = new File();
        $file
            ->setName($uploadedFile->getClientOriginalName())
            ->setSize($uploadedFile->getSize())
            ->setCachedAt(new \DateTimeImmutable($this->bag->get('duration_cached_files')))
        ;

        $fileName = $this->sanitizeAndUniqueFileName($uploadedFile);
        $savedFile = $uploadedFile->move($this->getAndCreateDestinationDirectory($structure), $fileName);

        $pathFile = $savedFile->getRealPath();

        $file->setPath($pathFile);
        $this->em->persist($file);

        $this->transfertToS3($pathFile);

        return $file;
    }

    public function transfertToS3(string $path)
    {
        if (false === $this->fileExist($path)) {
            $errorMessage = "not find path ($path)";

            $this->logger->error($errorMessage);
            throw new NotFoundResourceException($errorMessage);
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

    public function deleteConvocationAndInvitationFiles(Sitting $sitting, bool $isDeleteS3 = true)
    {
        $this->delete($sitting->getConvocationFile(), $isDeleteS3);
        $this->delete($sitting->getInvitationFile(), $isDeleteS3);
    }

    public function delete(?File $file, bool $isDeleteS3 = true): void
    {
        if (!$file) {
            return;
        }

        $filePath = $file->getPath();

        $this->filesystem->remove($filePath);

        if ($isDeleteS3) {
            $this->s3Manager->deleteObject($filePath);
        }

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

    public function updateCachedAt(File $file): void
    {
        $file->setCachedAt(new \DateTimeImmutable($this->bag->get('duration_cached_files')));

        $this->em->persist($file);
        $this->em->flush();
    }

    public function removeCachedAt(File $file): void
    {
        $file->setCachedAt(null);

        $this->em->persist($file);
        $this->em->flush();
    }
}
