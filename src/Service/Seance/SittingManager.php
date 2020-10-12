<?php


namespace App\Service\Seance;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Service\Convocation\ConvocationManager;
use App\Service\File\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SittingManager
{
    private ConvocationManager $convocationManager;
    private FileManager $fileManager;
    private EntityManagerInterface $em;

    public function __construct(ConvocationManager $convocationManager, FileManager $fileManager, EntityManagerInterface $em)
    {
        $this->convocationManager = $convocationManager;
        $this->fileManager = $fileManager;
        $this->em = $em;
    }

    public function save(Sitting $sitting, UploadedFile $uploadedFile, Structure $structure)
    {
        // TODO remove file if transaction failed
        $convocationFile = $this->fileManager->save($uploadedFile, $structure);

        $sitting->setStructure($structure)
        ->setName($sitting->getType()->getName() . uniqid())
            ->setFile($convocationFile);
        $this->em->persist($sitting);

        $this->convocationManager->createConvocations($sitting);
        $this->em->flush();
    }

    public function delete(Sitting $sitting)
    {
        $this->fileManager->delete($sitting->getFile());
        $this->em->remove($sitting);
        $this->em->flush();
    }
}
