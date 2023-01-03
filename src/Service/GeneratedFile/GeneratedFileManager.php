<?php

namespace App\Service\GeneratedFile;

use App\Entity\File;
use App\Entity\GeneratedFile;
use App\Entity\Sitting;
use App\Repository\GeneratedFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GeneratedFileManager
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ParameterBagInterface $bag,
        private readonly GeneratedFileRepository $generatedFileRepository,
    ) {
    }

    public function addOrReplace(string $type, Sitting $sitting, string $pathFilePdf): void
    {
        $generatedFile = $this->generatedFileRepository->getGeneratedFileBySitting($sitting, $type);
        if ($generatedFile) {
            $this->delete($generatedFile);
        }

        $file = (new File())
            ->setName($sitting->getId() . '.pdf')
            ->setPath($pathFilePdf)
            ->setSize(filesize($pathFilePdf))
            ->setCachedAt(new \DateTimeImmutable($this->bag->get('duration_cached_files')))
        ;

        $generatedFile = new GeneratedFile(
            $type,
            $sitting,
            $file
        );
        $this->save($generatedFile);
    }

    private function save(GeneratedFile $generatedFile): void
    {
        $this->em->persist($generatedFile);
        $this->em->flush();
    }

    private function delete(GeneratedFile $generatedFile): void
    {
        $this->em->remove($generatedFile);
        $this->em->flush();
    }

    private function remove(GeneratedFile $generatedFile): void
    {
        $this->em->remove($generatedFile);
    }

    public function deleteGeneratedFile(Sitting $sitting, $type): void
    {
        $generatedFile = $this->generatedFileRepository->getGeneratedFileBySitting($sitting, $type);

        if ($generatedFile) {
            $this->remove($generatedFile);
        }
    }
}