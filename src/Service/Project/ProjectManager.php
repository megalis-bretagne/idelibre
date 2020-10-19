<?php


namespace App\Service\Project;


use App\Entity\Project;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Theme;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\ThemeRepository;
use App\Repository\UserRepository;
use App\Service\ApiEntity\ProjectApi;
use App\Service\File\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProjectManager
{

    private ProjectRepository $projectRepository;
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;
    /**
     * @var ThemeRepository
     */
    private ThemeRepository $themeRepository;
    /**
     * @var FileManager
     */
    private FileManager $fileManager;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ProjectRepository $projectRepository,
                                UserRepository $userRepository,
                                ThemeRepository $themeRepository,
                                FileManager $fileManager,
                                EntityManagerInterface $em)
    {
        $this->projectRepository = $projectRepository;
        $this->userRepository = $userRepository;
        $this->themeRepository = $themeRepository;
        $this->fileManager = $fileManager;
        $this->em = $em;
    }

    /**
     * @param ProjectApi[] $clientProjects
     * @param UploadedFile[] $uploadedFiles
     */
    public function update(array $clientProjects, array $uploadedFiles, Sitting $sitting)
    {
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

        return $this->updateProject($clientProject);

    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    private function createProject(ProjectApi $clientProject, array $uploadedFiles, Sitting $sitting): Project
    {
        if (!isset($uploadedFiles[$clientProject->getLinkedFile()])) {
            throw new BadRequestException('Le fichier associé est obligatoire');
        }
        $uploadedFile = $uploadedFiles[$clientProject->getLinkedFile()];

        $project = new Project();
        $project->setName($clientProject->getName())
            ->setRank($clientProject->getRank())
            ->setReporter($this->getReporter($clientProject->getReporterId()))
            ->setTheme($this->getTheme($clientProject->getThemeId()))
            ->setSitting($sitting)
            ->setFile($this->fileManager->save($uploadedFile, $sitting->getStructure()))
            ->setAnnexes($this->createAnnexes($clientProject, $uploadedFiles, $sitting->getStructure()));


        // TODO validate

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


    private function updateProject(ProjectApi $clientProject): Project
    {
        $project = $this->projectRepository->findOneBy(['id' => $clientProject->getId()]);
        if (!$project) {
            throw new BadRequestException('le projet n\'existe pas');
        }

        // Todo update project
        return $project;
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    private function createAnnexes(ProjectApi $clientProject, array $uploadedFiles, Structure $structure)
    {
    }
}
