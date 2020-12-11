<?php

namespace App\Service\Seance;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Message\UpdatedSitting;
use App\Service\Convocation\ConvocationManager;
use App\Service\File\FileManager;
use App\Service\Project\ProjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class SittingManager
{
    private ConvocationManager $convocationManager;
    private FileManager $fileManager;
    private EntityManagerInterface $em;

    private MessageBusInterface $messageBus;

    private ProjectManager $projectManager;

    public function __construct(
        ConvocationManager $convocationManager,
        FileManager $fileManager,
        EntityManagerInterface $em,
        MessageBusInterface $messageBus,
        ProjectManager $projectManager
    ) {
        $this->convocationManager = $convocationManager;
        $this->fileManager = $fileManager;
        $this->em = $em;
        $this->messageBus = $messageBus;
        $this->projectManager = $projectManager;
    }

    public function save(Sitting $sitting, UploadedFile $uploadedFile, Structure $structure): string
    {
        // TODO remove file if transaction failed
        $convocationFile = $this->fileManager->save($uploadedFile, $structure);

        $sitting->setStructure($structure)
            ->setName($sitting->getType()->getName())
            ->setFile($convocationFile);
        $this->em->persist($sitting);

        $this->convocationManager->createConvocations($sitting);
        $this->em->flush();

        $this->messageBus->dispatch(new UpdatedSitting($sitting->getId()));

        return $sitting->getId();
    }

    public function delete(Sitting $sitting): void
    {
        $this->fileManager->delete($sitting->getFile());
        $this->projectManager->deleteProjects($sitting->getProjects());
        $this->convocationManager->deleteConvocations($sitting->getConvocations());
        $this->em->remove($sitting);
        $this->em->flush();
        // TODO remove fullpdf and zip !
    }

    public function update(Sitting $sitting, ?UploadedFile $uploadedFile): void
    {
        if ($uploadedFile) {
            $convocationFile = $this->fileManager->replace($uploadedFile, $sitting);
            $sitting->setFile($convocationFile);
        }
        $this->em->persist($sitting);
        $this->em->flush();

        $this->messageBus->dispatch(new UpdatedSitting($sitting->getId()));
    }

    public function archive(Sitting $sitting): void
    {
        $sitting->setIsArchived(true);
        $this->convocationManager->deactivate($sitting->getConvocations());
        $this->em->persist($sitting);
        $this->em->flush();
    }
}
