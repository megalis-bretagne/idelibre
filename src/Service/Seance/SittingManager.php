<?php


namespace App\Service\Seance;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use App\Message\UpdatedSitting;
use App\Service\Convocation\ConvocationManager;
use App\Service\File\FileManager;
use App\Service\Project\ProjectManager;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class SittingManager
{
    private ConvocationManager $convocationManager;
    private FileManager $fileManager;
    private EntityManagerInterface $em;
    /**
     * @var MessageBusInterface
     */
    private MessageBusInterface $messageBus;
    /**
     * @var ProjectManager
     */
    private ProjectManager $projectManager;

    public function __construct(ConvocationManager $convocationManager, FileManager $fileManager, EntityManagerInterface $em, MessageBusInterface $messageBus, ProjectManager $projectManager)
    {
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

    public function delete(Sitting $sitting)
    {
        $this->fileManager->deletse($sitting->getFile());
        $this->projectManager->deleteProjects($sitting->getProjects());
        $this->em->remove($sitting);
        $this->em->flush();
        // TODO remove fullpdf and zip !
    }

    public function update(Sitting $sitting, ?UploadedFile $uploadedFile)
    {
        if ($uploadedFile) {
            $convocationFile = $this->fileManager->replace($uploadedFile, $sitting);
            $sitting->setFile($convocationFile);
        }
        $this->em->persist($sitting);
        $this->em->flush();

        $this->messageBus->dispatch(new UpdatedSitting($sitting->getId()));
    }
}
