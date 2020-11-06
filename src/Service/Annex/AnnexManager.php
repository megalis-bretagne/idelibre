<?php


namespace App\Service\Annex;

use App\Entity\Annex;
use App\Entity\Sitting;
use App\Repository\AnnexRepository;
use App\Service\ApiEntity\ProjectApi;
use App\Service\File\FileManager;
use Doctrine\ORM\EntityManagerInterface;

class AnnexManager
{
    private EntityManagerInterface $em;
    private FileManager $fileManager;
    /**
     * @var AnnexRepository
     */
    private AnnexRepository $annexRepository;

    public function __construct(EntityManagerInterface $em, FileManager $fileManager, AnnexRepository $annexRepository)
    {
        $this->em = $em;
        $this->fileManager = $fileManager;
        $this->annexRepository = $annexRepository;
    }

    /**
     * @param Annex[] $annexes
     */
    public function deleteAnnexes(iterable $annexes)
    {
        foreach ($annexes as $annex) {
            $this->fileManager->delete($annex->getFile());
            $this->em->remove($annex);
        }
    }

    /**
     * @param ProjectApi[] $clientProjects
     */
    public function deleteRemovedAnnexe(array $clientProjects, Sitting $sitting)
    {
        $toDeleteAnnexes = $this->annexRepository->findNotInListAnnexes($this->listClientAnnexeIds($clientProjects), $sitting);
        $this->deleteAnnexes($toDeleteAnnexes);
    }

    /**
     * @param ProjectApi[] $clientProjects
     */
    private function listClientAnnexeIds(array $clientProjects):array
    {
        $annexIds = [];
        foreach ($clientProjects as $clientProject) {
            if ($clientProject->getId()) {
                foreach ($clientProject->getAnnexes() as $annex) {
                    if ($annex->getId()) {
                        $annexIds[] = $annex->getId();
                    }
                }
            }
        }
        return $annexIds;
    }
}
