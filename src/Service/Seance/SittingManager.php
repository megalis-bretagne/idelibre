<?php

namespace App\Service\Seance;

use App\Entity\Reminder;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\User;
use App\Message\UpdatedSitting;
use App\Repository\ProjectRepository;
use App\Repository\SittingRepository;
use App\Service\Convocation\ConvocationManager;
use App\Service\File\FileManager;
use App\Service\Pdf\PdfSittingGenerator;
use App\Service\Project\ProjectManager;
use App\Service\role\RoleManager;
use App\Service\Zip\ZipSittingGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class SittingManager
{
    public function __construct(
        private ConvocationManager $convocationManager,
        private FileManager $fileManager,
        private EntityManagerInterface $em,
        private MessageBusInterface $messageBus,
        private ProjectManager $projectManager,
        private RoleManager $roleManager,
        private SittingRepository $sittingRepository,
        private PdfSittingGenerator $pdfSittingGenerator,
        private ZipSittingGenerator $zipSittingGenerator,
        private ProjectRepository $projectRepository
    ) {
    }

    public function save(
        Sitting $sitting,
        UploadedFile $uploadedConvocationFile,
        ?UploadedFile $uploadedInvitationFile,
        Structure $structure,
        ?Reminder $reminder = null
    ): string {
        // TODO remove file if transaction failed
        $convocationFile = $this->fileManager->save($uploadedConvocationFile, $structure);

        $sitting->setStructure($structure)
            ->setName($sitting->getType()->getName())
            ->setConvocationFile($convocationFile);
        $this->em->persist($sitting);

        if ($reminder) {
            $this->em->persist($reminder);
            $sitting->setReminder($reminder);
        }

        $this->convocationManager->createConvocationsActors($sitting);
        $this->createInvitationsInvitableEmployeesAndGuests($uploadedInvitationFile, $sitting, $structure);

        $this->em->flush();

        $this->messageBus->dispatch(new UpdatedSitting($sitting->getId()));

        return $sitting->getId();
    }

    private function createInvitationsInvitableEmployeesAndGuests(
        ?UploadedFile $uploadedInvitationFile,
        Sitting $sitting,
        Structure $structure
    ) {
        if ($uploadedInvitationFile) {
            $invitationFile = $this->fileManager->save($uploadedInvitationFile, $structure);
            $this->convocationManager->createConvocationsInvitableEmployees($sitting);
            $this->convocationManager->createConvocationsGuests($sitting);
            $sitting->setInvitationFile($invitationFile);
        }
    }

    public function delete(Sitting $sitting): void
    {
        $this->fileManager->delete($sitting->getConvocationFile());
        $this->projectManager->deleteProjects($sitting->getProjects());
        $this->convocationManager->deleteConvocations($sitting->getConvocations());
        $this->em->remove($sitting);
        $this->em->flush();

        $this->pdfSittingGenerator->deletePdf($sitting);
        $this->zipSittingGenerator->deleteZip($sitting);
    }

    public function update(Sitting $sitting, ?UploadedFile $uploadedConvocationFile, ?UploadedFile $uploadedInvitationFile): void
    {
        if ($uploadedConvocationFile) {
            $convocationFile = $this->fileManager->replaceConvocationFile($uploadedConvocationFile, $sitting);
            $sitting->setConvocationFile($convocationFile);
        }

        if ($uploadedInvitationFile) {
            $this->createOrReplaceInvitation($uploadedInvitationFile, $sitting);
        }

        $this->em->persist($sitting);
        $this->em->flush();

        $this->messageBus->dispatch(new UpdatedSitting($sitting->getId()));
    }

    private function createOrReplaceInvitation(UploadedFile $uploadedInvitationFile, Sitting $sitting)
    {
        if (!$sitting->getInvitationFile()) {
            $this->createInvitationsInvitableEmployeesAndGuests($uploadedInvitationFile, $sitting, $sitting->getStructure());

            return;
        }

        $invitationFile = $this->fileManager->replaceInvitationFile($uploadedInvitationFile, $sitting);
        $sitting->setInvitationFile($invitationFile);
    }

    public function archive(Sitting $sitting): void
    {
        $sitting->setIsArchived(true);
        $this->convocationManager->deactivate($sitting->getConvocations());
        $this->em->persist($sitting);
        $this->em->flush();
    }

    public function unArchive(Sitting $sitting)
    {
        $sitting->setIsArchived(false);
        $this->convocationManager->reactivate($sitting->getConvocations());
        $this->em->persist($sitting);
        $this->em->flush();
    }

    public function getListSittingByStructureQuery(User $user, ?string $search, ?string $status): QueryBuilder
    {
        if ($user->getRole()->getId() === $this->roleManager->getSecretaryRole()->getId()) {
            return $this->sittingRepository->findWithTypesByStructure($user->getStructure(), $user->getAuthorizedTypes(), $search, $status);
        }

        return $this->sittingRepository->findByStructure($user->getStructure(), $search, $status);
    }

    public function getActiveSittingDetails(User $user): QueryBuilder
    {
        if ($user->getRole()->getId() === $this->roleManager->getSecretaryRole()->getId()) {
            return $this->sittingRepository->findActiveFromStructure($user->getStructure(), $user->getAuthorizedTypes());
        }

        return $this->sittingRepository->findByStructure($user->getStructure());
    }

    public function isAlreadySent(Sitting $sitting): bool
    {
        foreach ($sitting->getConvocations() as $convocation) {
            if ($convocation->getSentTimestamp()) {
                return true;
            }
        }

        return false;
    }

    public function isAlreadySentConvocation(Sitting $sitting): bool
    {
        foreach ($sitting->getConvocations() as $convocation) {
            if ($convocation->getIsActive() && $convocation->isConvocation()) {
                return true;
            }
        }

        return false;
    }

    public function isAlreadySentInvitation(Sitting $sitting): bool
    {
        foreach ($sitting->getConvocations() as $convocation) {
            if ($convocation->getIsActive() && $convocation->isInvitation()) {
                return true;
            }
        }

        return false;
    }

    public function reorderProjects(Sitting $sitting): void
    {
        $projects = $this->projectRepository->getProjectsBySitting($sitting);

        foreach ($projects as $pos => $project) {
            $project->setRank($pos);
            $this->em->persist($project);
        }

        $this->em->flush();
    }
}
