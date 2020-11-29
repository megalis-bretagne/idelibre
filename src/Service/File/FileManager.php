<?php


namespace App\Service\File;

use App\Entity\File;
use App\Entity\Sitting;
use App\Entity\Structure;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManager
{
    private Filesystem $filesystem;
    private EntityManagerInterface $em;
    private ParameterBagInterface $bag;


    public function __construct(Filesystem $filesystem, EntityManagerInterface $em, ParameterBagInterface $bag)
    {
        $this->filesystem = $filesystem;
        $this->em = $em;
        $this->bag = $bag;
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

    public function delete(?File $file):void
    {
        if (!$file) {
            return;
        }
        $this->filesystem->remove($file->getPath());
        $this->em->remove($file);
    }


    public function replace(UploadedFile $uploadedFile, Sitting $sitting): File
    {
        $this->delete($sitting->getFile());
        return $this->save($uploadedFile, $sitting->getStructure());
    }
}
