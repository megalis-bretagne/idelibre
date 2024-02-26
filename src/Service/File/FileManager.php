<?php

namespace App\Service\File;

use App\Entity\Annex;
use App\Entity\File;
use App\Entity\Project;
use App\Entity\Sitting;
use App\Entity\Structure;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManager
{
    public function __construct(
        private Filesystem $filesystem,
        private EntityManagerInterface $em,
        private ParameterBagInterface $bag,
    ) {
    }

    public function save(UploadedFile $uploadedFile, Structure $structure): File
    {
        $file = new File();
        $file->setName($uploadedFile->getClientOriginalName())
            ->setSize($uploadedFile->getSize());

        $fileName = $this->sanitizeAndUniqueFileName($uploadedFile);
        $savedFile = $uploadedFile->move($this->getAndCreateDestinationDirectory($structure), $fileName);

        $file->setPath($savedFile->getRealPath());
        $this->em->persist($file);

        return $file;
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
    public function addProjectsAndAnnexes(iterable $projects): array
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

    public function saveImage(Structure $structure, string $extension): string
    {
        $imgPath = '/data/image/' . $structure->getId();

        $file = new File();
        $this->em->persist($file);
        $file->setPath($imgPath . '/' . $file->getId() . '.' . $extension  );
        $file->setName($file->getId() . '.' . $extension);
        $this->em->persist($file);
        $this->em->flush();

        return $file->getId() . '.' . $extension;
    }
}
