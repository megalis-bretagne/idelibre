<?php

namespace App\Service\Seance;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\User;
use App\Message\UpdatedSitting;
use App\Repository\SittingRepository;
use App\Service\Convocation\ConvocationManager;
use App\Service\File\FileManager;
use App\Service\Project\ProjectManager;
use App\Service\role\RoleManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class SittingManager
{
    private ConvocationManager $convocationManager;
    private FileManager $fileManager;
    private EntityManagerInterface $em;
    private MessageBusInterface $messageBus;
    private ProjectManager $projectManager;
    private RoleManager $roleManager;
    private SittingRepository $sittingRepository;

    public function __construct(
        ConvocationManager $convocationManager,
        FileManager $fileManager,
        EntityManagerInterface $em,
        MessageBusInterface $messageBus,
        ProjectManager $projectManager,
        RoleManager $roleManager,
        SittingRepository $sittingRepository
    ) {
        $this->convocationManager = $convocationManager;
        $this->fileManager = $fileManager;
        $this->em = $em;
        $this->messageBus = $messageBus;
        $this->projectManager = $projectManager;
        $this->roleManager = $roleManager;
        $this->sittingRepository = $sittingRepository;
    }

    public function save(
        Sitting $sitting,
        UploadedFile $uploadedConvocationFile,
        ?UploadedFile $uploadedInvitationFile,
        Structure $structure
    ): string {
        // TODO remove file if transaction failed
        $convocationFile = $this->fileManager->save($uploadedConvocationFile, $structure);

        $sitting->setStructure($structure)
            ->setName($sitting->getType()->getName())
            ->setConvocationFile($convocationFile);
        $this->em->persist($sitting);

        if ($uploadedInvitationFile) {
            $invitationFile = $this->fileManager->save($uploadedInvitationFile, $structure);
            $sitting->setInvitationFile($invitationFile);
        }

        $this->convocationManager->createConvocations($sitting);
        $this->em->flush();

        $this->messageBus->dispatch(new UpdatedSitting($sitting->getId()));

        return $sitting->getId();
    }

    public function delete(Sitting $sitting): void
    {
        $this->fileManager->delete($sitting->getConvocationFile());
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
            $sitting->setConvocationFile($convocationFile);
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

    public function getListSittingByStructureQuery(User $user, ?string $search): QueryBuilder
    {
        if ($user->getRole()->getId() === $this->roleManager->getSecretaryRole()->getId()) {
            return $this->sittingRepository->findWithTypesByStructure($user->getStructure(), $user->getAuthorizedTypes(), $search);
        }

        return $this->sittingRepository->findByStructure($user->getStructure(), $search);
    }
}
