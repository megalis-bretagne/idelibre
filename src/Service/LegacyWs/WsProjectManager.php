<?php

namespace App\Service\LegacyWs;

use App\Entity\Annex;
use App\Entity\Project;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\File\FileManager;
use App\Service\Theme\ThemeManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class WsProjectManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ThemeManager $themeManager,
        private FileManager $fileManager,
        private UserRepository $userRepository
    ) {
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    public function createProjectsAndAnnexes(?array $rawProjects, array $uploadedFiles, Sitting $sitting): void
    {
        if (!$rawProjects) {
            return;
        }

        foreach ($rawProjects as $rawProject) {
            $this->validateRawProject($rawProject, $uploadedFiles);
            $rank = $rawProject['ordre'];
            $project = new Project();
            $project->setRank($rank)
                ->setSitting($sitting)
                ->setName($rawProject['libelle'])
                ->setReporter($this->getReporter($rawProject['Rapporteur'] ?? null, $sitting->getStructure()))
                ->setTheme($this->themeManager->createThemesFromString($rawProject['theme'], $sitting->getStructure()))
                ->setFile($this->fileManager->save($uploadedFiles['projet_' . $rank . '_rapport'], $sitting->getStructure()));
            $this->em->persist($project);
            $this->createAnnexes($rawProject['annexes'] ?? null, $uploadedFiles, $project);
        }
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    private function createAnnexes(?array $rawAnnexes, array $uploadedFiles, Project $project)
    {
        if (empty($rawAnnexes)) {
            return;
        }

        foreach ($rawAnnexes as $rawAnnex) {
            $this->validateRawAnnex($rawAnnex, $uploadedFiles, $project->getRank());
            $annexRank = $rawAnnex['ordre'];
            $projectRank = $project->getRank();
            $annex = new Annex();
            $annex->setRank($annexRank)
                ->setProject($project)
                ->setFile($this->fileManager->save($uploadedFiles["projet_${projectRank}_${annexRank}_annexe"], $project->getSitting()->getStructure()));
            $this->em->persist($annex);
        }
    }

    private function getReporter(?array $rawReporter, Structure $structure): ?User
    {
        if (empty($rawReporter)) {
            return null;
        }
        if (!isset($rawReporter['rapporteurlastname']) || !isset($rawReporter['rapporteurfirstname'])) {
            return null;
        }

        return $this->userRepository->findOneBy(
            ['firstName' => $rawReporter['rapporteurfirstname'], 'lastName' => $rawReporter['rapporteurlastname'], 'structure' => $structure]
        );
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    private function validateRawProject(array $rawProject, array $uploadedFiles): void
    {
        if (!isset($rawProject['ordre'])) {
            throw new BadRequestHttpException('projets[]["ordre"] is required');
        }

        if (!isset($rawProject['libelle'])) {
            throw new BadRequestHttpException('projets[]["libelle"] is required');
        }

        if (!isset($rawProject['theme'])) {
            throw new BadRequestHttpException('projets[]["theme"] is required');
        }

        $rank = $rawProject['ordre'];
        if (!isset($uploadedFiles['projet_' . $rank . '_rapport'])) {
            throw new BadRequestHttpException('file ' . 'projet_' . $rank . '_rapport' . ' is required');
        }
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    private function validateRawAnnex(array $rawAnnex, array $uploadedFiles, int $projectRank): void
    {
        if (!isset($rawAnnex['ordre'])) {
            throw new BadRequestHttpException('annexes[]["ordre"] is required');
        }

        $annexRank = $rawAnnex['ordre'];
        if (!isset($uploadedFiles["projet_${projectRank}_${annexRank}_annexe"])) {
            throw new BadRequestHttpException('file ' . "projet_${projectRank}_${annexRank}_annexe" . ' is required');
        }
    }
}
