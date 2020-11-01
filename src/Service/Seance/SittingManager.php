<?php


namespace App\Service\Seance;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use App\Message\GenZipSitting;
use App\Service\Convocation\ConvocationManager;
use App\Service\File\FileManager;
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

    public function __construct(ConvocationManager $convocationManager, FileManager $fileManager, EntityManagerInterface $em, MessageBusInterface $messageBus)
    {
        $this->convocationManager = $convocationManager;
        $this->fileManager = $fileManager;
        $this->em = $em;
        $this->messageBus = $messageBus;
    }

    public function save(Sitting $sitting, UploadedFile $uploadedFile, Structure $structure)
    {
        // TODO remove file if transaction failed
        $convocationFile = $this->fileManager->save($uploadedFile, $structure);

        $sitting->setStructure($structure)
        ->setName($sitting->getType()->getName())
            ->setFile($convocationFile);
        $this->em->persist($sitting);

        $this->convocationManager->createConvocations($sitting);
        $this->em->flush();

        $this->messageBus->dispatch(new GenZipSitting($sitting->getId()));
    }

    public function delete(Sitting $sitting)
    {
        $this->fileManager->delete($sitting->getFile());
        $this->em->remove($sitting);
        $this->em->flush();
    }

    public function update(Sitting $sitting, ?UploadedFile $uploadedFile)
    {
        if ($uploadedFile) {
            $convocationFile = $this->fileManager->replace($uploadedFile, $sitting);
            $sitting->setFile($convocationFile);
        }
        $this->em->persist($sitting);
        $this->em->flush();

        $this->messageBus->dispatch(new GenZipSitting($sitting->getId()));
    }
}
