<?php

namespace App\Service\Otherdoc;

use App\Entity\Otherdoc;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Repository\OtherdocRepository;
use App\Service\ApiEntity\OtherdocApi;
use App\Service\ClientNotifier\ClientNotifierInterface;
use App\Service\File\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class OtherdocManager
{
    public function __construct(
        private OtherdocRepository $otherdocRepository,
        private FileManager $fileManager,
        private EntityManagerInterface $em,
        private ClientNotifierInterface $clientNotifier
    ) {
    }

    /**
     * @param OtherdocApi[]  $clientOtherdocs
     * @param UploadedFile[] $uploadedFiles
     */
    public function update(array $clientOtherdocs, array $uploadedFiles, Sitting $sitting): void
    {
        $this->deleteRemovedOtherdocs($clientOtherdocs, $sitting);
        foreach ($clientOtherdocs as $clientOtherdoc) {
            $this->createOrUpdateOtherdoc($clientOtherdoc, $uploadedFiles, $sitting);
        }
        $sitting->setRevision($sitting->getRevision() + 1);
        $this->em->flush();
        $this->clientNotifier->modifiedSittingNotification($sitting->getConvocations()->toArray());
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    private function createOrUpdateOtherdoc(OtherdocApi $clientOtherdoc, array $uploadedFiles, Sitting $sitting): Otherdoc
    {
        if (!$clientOtherdoc->getId()) {
            return $this->createOtherdoc($clientOtherdoc, $uploadedFiles, $sitting);
        }

        return $this->updateOtherdoc($clientOtherdoc, $uploadedFiles, $sitting->getStructure());
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    private function createOtherdoc(OtherdocApi $clientOtherdoc, array $uploadedFiles, Sitting $sitting): Otherdoc
    {
        if (!isset($uploadedFiles[$clientOtherdoc->getLinkedFileKey()])) {
            throw new BadRequestException('Le fichier associé est obligatoire');
        }
        $uploadedFile = $uploadedFiles[$clientOtherdoc->getLinkedFileKey()];

        $otherdoc = new Otherdoc();
        $otherdoc->setName($clientOtherdoc->getName())
            ->setRank($clientOtherdoc->getRank())
            ->setSitting($sitting)
            ->setFile($this->fileManager->save($uploadedFile, $sitting->getStructure()));

        $this->em->persist($otherdoc);

        return $otherdoc;
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    private function updateOtherdoc(OtherdocApi $clientOtherdoc, array $uploadedFiles, Structure $structure): Otherdoc
    {
        $otherdoc = $this->otherdocRepository->findOneBy(['id' => $clientOtherdoc->getId()]);
        if (!$otherdoc) {
            throw new BadRequestException('le projet n\'existe pas');
        }

        $otherdoc->setName($clientOtherdoc->getName())
            ->setRank($clientOtherdoc->getRank());

        $this->em->persist($otherdoc);

        return $otherdoc;
    }

    /**
     * @return Otherdoc[]
     */
    public function getOtherdocsFromSitting(Sitting $sitting): iterable
    {
        return $this->otherdocRepository->getOtherdocsWithAssociatedEntities($sitting);
    }

    /**
     * @param otherdoc[] $otherdocs
     *
     * @return OtherdocApi[]
     */
    public function getApiOtherdocsFromOtherdocs(iterable $otherdocs): array
    {
        $apiOtherdocs = [];
        foreach ($otherdocs as $otherdoc) {
            $apiOtherdoc = new OtherdocApi();
            $apiOtherdoc->setName($otherdoc->getName())
                ->setRank($otherdoc->getRank())
                ->setFileName($otherdoc->getFile()->getName())
                ->setId($otherdoc->getId())
                ->setSize($otherdoc->getFile()->getSize());
            $apiOtherdocs[] = $apiOtherdoc;
        }

        return $apiOtherdocs;
    }

    /**
     * @param OtherdocApi[] $clientOtherdocs
     */
    private function deleteRemovedOtherdocs(array $clientOtherdocs, Sitting $sitting): void
    {
        $toDeleteOtherdocs = $this->otherdocRepository->findNotInListOtherdocs($this->listClientOtherdocIds($clientOtherdocs), $sitting);
        $this->deleteOtherdocs($toDeleteOtherdocs);
    }

    /**
     * @param Otherdoc[] $otherdocs
     */
    public function deleteOtherdocs(iterable $otherdocs): void
    {
        foreach ($otherdocs as $otherdoc) {
            $this->fileManager->delete($otherdoc->getFile());
            $this->em->remove($otherdoc);
        }
    }

    /**
     * @param OtherdocApi[] $clientOtherdocs
     *
     * @return string[]
     */
    private function listClientOtherdocIds(array $clientOtherdocs): array
    {
        $ids = [];
        foreach ($clientOtherdocs as $clientOtherdoc) {
            if ($clientOtherdoc->getId()) {
                $ids[] = $clientOtherdoc->getId();
            }
        }

        return $ids;
    }
}
