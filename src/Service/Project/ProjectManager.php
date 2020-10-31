<?php


namespace App\Service\Project;

use App\Entity\Annex;
use App\Entity\Project;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Theme;
use App\Entity\User;
use App\Repository\AnnexRepository;
use App\Repository\ProjectRepository;
use App\Repository\ThemeRepository;
use App\Repository\UserRepository;
use App\Service\Annex\AnnexManager;
use App\Service\ApiEntity\AnnexApi;
use App\Service\ApiEntity\ProjectApi;
use App\Service\File\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProjectManager
{
    private ProjectRepository $projectRepository;
    private UserRepository $userRepository;
    private ThemeRepository $themeRepository;
    private FileManager $fileManager;
    private EntityManagerInterface $em;
    private AnnexRepository $annexRepository;
    private AnnexManager $annexManager;

    public function __construct(
        ProjectRepository $projectRepository,
        UserRepository $userRepository,
        AnnexRepository $annexRepository,
        ThemeRepository $themeRepository,
        FileManager $fileManager,
        AnnexManager $annexManager,
        EntityManagerInterface $em
    )
    {
        $this->projectRepository = $projectRepository;
        $this->userRepository = $userRepository;
        $this->themeRepository = $themeRepository;
        $this->fileManager = $fileManager;
        $this->em = $em;
        $this->annexRepository = $annexRepository;
        $this->annexManager = $annexManager;
    }

    /**
     * @param ProjectApi[] $clientProjects
     * @param UploadedFile[] $uploadedFiles
     */
    public function update(array $clientProjects, array $uploadedFiles, Sitting $sitting)
    {
        $this->annexManager->deleteRemovedAnnexe($clientProjects, $sitting);
        $this->deleteRemovedProjects($clientProjects, $sitting);
        foreach ($clientProjects as $clientProject) {
            $this->createOrUpdateProject($clientProject, $uploadedFiles, $sitting);
        }
        $this->em->flush();
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    private function createOrUpdateProject(ProjectApi $clientProject, array $uploadedFiles, Sitting $sitting): Project
    {
        if (!$clientProject->getId()) {
            return $this->createProject($clientProject, $uploadedFiles, $sitting);
        }

        return $this->updateProject($clientProject, $uploadedFiles, $sitting->getStructure());
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    private function createProject(ProjectApi $clientProject, array $uploadedFiles, Sitting $sitting): Project
    {
        if (!isset($uploadedFiles[$clientProject->getLinkedFileKey()])) {
            throw new BadRequestException('Le fichier associé est obligatoire');
        }
        $uploadedFile = $uploadedFiles[$clientProject->getLinkedFileKey()];

        $project = new Project();
        $project->setName($clientProject->getName())
            ->setRank($clientProject->getRank())
            ->setReporter($this->getReporter($clientProject->getReporterId()))
            ->setTheme($this->getTheme($clientProject->getThemeId()))
            ->setSitting($sitting)
            ->setFile($this->fileManager->save($uploadedFile, $sitting->getStructure()));

        $this->createAndAddAnnexesToProject($project, $clientProject->getAnnexes(), $uploadedFiles, $sitting->getStructure());

        $this->em->persist($project);

        return $project;
    }


    private function getReporter(?string $reporterId): ?User
    {
        if (!$reporterId) {
            return null;
        }
        return $this->userRepository->findOneBy(['id' => $reporterId]);
    }


    private function getTheme(?string $themeId): ?Theme
    {
        if (!$themeId) {
            return null;
        }
        return $this->themeRepository->findOneBy(['id' => $themeId]);
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    private function updateProject(ProjectApi $clientProject, array $uploadedFiles, Structure $structure): Project
    {
        $project = $this->projectRepository->findOneBy(['id' => $clientProject->getId()]);
        if (!$project) {
            throw new BadRequestException('le projet n\'existe pas');
        }

        $project->setName($clientProject->getName())
            ->setRank($clientProject->getRank())
            ->setReporter($this->getReporter($clientProject->getReporterId()))
            ->setTheme($this->getTheme($clientProject->getThemeId()));

        $this->createOrUpdateAnnexes($project, $clientProject->getAnnexes(), $uploadedFiles, $structure);

        $this->em->persist($project);


        return $project;
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     * @param AnnexApi[] $clientAnnexes
     */
    private function createAndAddAnnexesToProject(Project $project, array $clientAnnexes, array $uploadedFiles, Structure $structure)
    {
        foreach ($clientAnnexes as $clientAnnex) {
            if (!isset($uploadedFiles[$clientAnnex->getLinkedFileKey()])) {
                throw new BadRequestException('Le fichier de l\'annexe n\'hexiste pas');
            }

            $this->createAndAddAnnex($project, $clientAnnex, $uploadedFiles, $structure);
        }
    }


    /**
     * @param Sitting $sitting
     * @return iterable
     */
    public function getProjectsFromSitting(Sitting $sitting): iterable
    {
        return $this->projectRepository->getProjectsWithAssociatedEntities($sitting);
    }

    /**
     * @param project[] $projects
     * @return ProjectApi[]
     */
    public function getApiProjectsFromProjects(iterable $projects): array
    {
        $apiProjects = [];
        foreach ($projects as $project) {
            $apiProject = new ProjectApi();
            $apiProject->setName($project->getName())
                ->setRank($project->getRank())
                ->setThemeId($project->getTheme() ? $project->getTheme()->getId() : null)
                ->setReporterId($project->getReporter() ? $project->getReporter()->getId() : null)
                ->setFileName($project->getFile()->getName())
                ->setId($project->getId())
                ->setAnnexes($this->getApiAnnexesFromAnnexes($project->getAnnexes()));
            $apiProjects[] = $apiProject;
        }
        return $apiProjects;
    }


    /**
     * @param annex[] $annexes
     * @return AnnexApi[]
     */
    public function getApiAnnexesFromAnnexes(iterable $annexes): array
    {
        $apiAnnexes = [];
        foreach ($annexes as $annex) {
            $annexApi = new AnnexApi();
            $annexApi->setRank($annex->getRank())
                ->setId($annex->getId())
                ->setFileName($annex->getFile()->getName());
            $apiAnnexes[] = $annexApi;
        }
        return $apiAnnexes;
    }

    /**
     * @param AnnexApi[] $clientAnnexes
     * @param UploadedFile[] $uploadedFiles
     */
    private function createOrUpdateAnnexes(Project $project, array $clientAnnexes, array $uploadedFiles, Structure $structure)
    {
        foreach ($clientAnnexes as $clientAnnex) {
            if (!$clientAnnex->getId()) {
                $this->createAndAddAnnex($project, $clientAnnex, $uploadedFiles, $structure);
                continue;
            }
            $this->updateAnnex($clientAnnex);
        }
    }


    private function updateAnnex(AnnexApi $clientAnnex)
    {
        $annex = $this->annexRepository->findOneBy(['id' => $clientAnnex->getId()]);
        if (!$annex) {
            throw new BadRequestException('l\'annexe n\'existe pas');
        }

        $annex->setRank($clientAnnex->getRank());
        $this->em->persist($annex);
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    private function createAndAddAnnex(Project $project, AnnexApi $clientAnnex, array $uploadedFiles, Structure $structure): void
    {
        $annex = new Annex();
        $annex->setRank($clientAnnex->getRank())
            ->setProject($project)
            ->setFile($this->fileManager->save($uploadedFiles[$clientAnnex->getLinkedFileKey()], $structure));
        $this->em->persist($annex);
    }

    /**
     * @param ProjectApi[] $clientProjects
     */
    private function deleteRemovedProjects(array $clientProjects, Sitting $sitting)
    {
        $toDeleteProjects = $this->projectRepository->findNotInListProjects($this->listClientProjectIds($clientProjects), $sitting);
        $this->deleteProjects($toDeleteProjects);
    }


    /**
     * @param Project[] $projects
     */
    private function deleteProjects(iterable $projects)
    {
        foreach ($projects as $project) {
            $this->annexManager->deleteAnnexes($project->getAnnexes());
            $this->fileManager->delete($project->getFile());
            $this->em->remove($project);
        }
    }


    /**
     * @param ProjectApi[] $clientProjects
     */
    private function listClientProjectIds(array $clientProjects): array
    {
        $ids = [];
        foreach ($clientProjects as $clientProject) {
            if ($clientProject->getId()) {
                $ids[] = $clientProject->getId();
            }
        }

        return $ids;
    }
}
