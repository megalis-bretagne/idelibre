<?php

namespace App\Service\Seance;

use App\Entity\Reminder;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\User;
use App\Message\UpdatedSitting;
use App\Repository\AnnotationRepository;
use App\Repository\OtherdocRepository;
use App\Repository\ProjectRepository;
use App\Repository\SittingRepository;
use App\Service\Convocation\ConvocationManager;
use App\Service\File\FileManager;
use App\Service\File\Generator\FileGenerator;
use App\Service\File\Generator\UnsupportedExtensionException;
use App\Service\Project\ProjectManager;
use App\Service\role\RoleManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class SittingManager
{
    public function __construct(
        private readonly ConvocationManager     $convocationManager,
        private readonly FileManager            $fileManager,
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface   $messageBus,
        private readonly ProjectManager         $projectManager,
        private readonly RoleManager            $roleManager,
        private readonly SittingRepository      $sittingRepository,
        private readonly FileGenerator          $fileGenerator,
        private readonly ProjectRepository      $projectRepository,
        private readonly OtherdocRepository     $otherdocRepository,
        private readonly AnnotationRepository   $annotationRepository,
    ) {
    }

    public const COEFFICIENT_CORRECTEUR = 1.17647058824;

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
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

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    private function createInvitationsInvitableEmployeesAndGuests(
        ?UploadedFile $uploadedInvitationFile,
        Sitting $sitting,
        Structure $structure
    ): void {
        if ($uploadedInvitationFile) {
            $invitationFile = $this->fileManager->save($uploadedInvitationFile, $structure);
            $this->convocationManager->createConvocationsInvitableEmployees($sitting);
            $this->convocationManager->createConvocationsGuests($sitting);
            $sitting->setInvitationFile($invitationFile);
        }
    }

    /**
     * @throws UnsupportedExtensionException
     */
    public function deleteByStructure(Structure $structure): void
    {
        $sittings = $this->sittingRepository->findByStructure($structure)->getQuery()->getResult();
        foreach ($sittings as $sitting) {
            $this->delete($sitting);
        }
    }

    /**
     * @throws UnsupportedExtensionException
     */
    public function delete(Sitting $sitting): void
    {
        $this->fileManager->delete($sitting->getConvocationFile());
        $this->fileManager->delete($sitting->getInvitationFile());
        $this->projectManager->deleteProjects($sitting->getProjects());
        $this->convocationManager->deleteConvocations($sitting->getConvocations());

        $this->fileGenerator->deleteFullSittingFile($sitting, 'pdf');
        $this->fileGenerator->deleteFullSittingFile($sitting, 'zip');

        $this->em->remove($sitting);
        $this->em->flush();
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

    private function createOrReplaceInvitation(UploadedFile $uploadedInvitationFile, Sitting $sitting): void
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
        ;
        $this->removeAnnotations($sitting);
        $sitting->setIsArchived(true);
        $this->convocationManager->deactivate($sitting->getConvocations());
        $this->em->persist($sitting);
        $this->em->flush();
    }

    public function unArchive(Sitting $sitting): void
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

    public function getProjectsAndAnnexesTotalSize(Sitting $sitting): int
    {
        $projects = $this->projectRepository->getProjectsBySitting($sitting);
        $total = 0;

        foreach ($projects as $project) {
            $total += $project->getFile()->getSize();

            foreach ($project->getAnnexes() as $annex) {
                $total += $annex->getFile()->getSize();
            }
        }

        return $total * self::COEFFICIENT_CORRECTEUR;
    }

    public function getOtherDocsTotalSize(Sitting $sitting): int
    {
        $otherDocs = $this->otherdocRepository->getOtherdocsBySitting($sitting);
        $total = 0;

        foreach ($otherDocs as $otherDoc) {
            $total += $otherDoc->getFile()->getSize();
        }

        return $total * self::COEFFICIENT_CORRECTEUR;
    }

    public function getAllFilesSize(Sitting $sitting): int
    {
        $total = 0;
        $total += $this->getProjectsAndAnnexesTotalSize($sitting);
        $total += $this->getOtherDocsTotalSize($sitting);

        return $total;
    }


    private function removeAnnotations($sitting): void
    {
        $annotations = $this->annotationRepository->findAnnotationBySitting($sitting);
        foreach ($annotations as $annotation) {
            $this->em->remove($annotation);
        }
    }

    public function removeInvitationFile(Sitting $sitting): void
    {
        $sitting->setInvitationFile(null);
        $this->em->persist($sitting);
        $this->em->flush();
    }
}
