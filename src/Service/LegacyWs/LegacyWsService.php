<?php

namespace App\Service\LegacyWs;

use App\Entity\Annex;
use App\Entity\Project;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\User;
use App\Repository\SittingRepository;
use App\Repository\StructureRepository;
use App\Repository\UserRepository;
use App\Security\Password\LegacyPassword;
use App\Service\Convocation\ConvocationManager;
use App\Service\File\FileManager;
use App\Service\Theme\ThemeManager;
use App\Service\Type\TypeManager;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LegacyWsService
{
    private UserRepository $userRepository;
    private TypeManager $typeManager;
    private FileManager $fileManager;
    private EntityManagerInterface $em;
    private ThemeManager $themeManager;
    private ConvocationManager $convocationManager;
    private SittingRepository $sittingRepository;
    private WsActorManager $wsActorManager;

    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        TypeManager $typeManager,
        FileManager $fileManager,
        ThemeManager $themeManager,
        ConvocationManager $convocationManager,
        SittingRepository $sittingRepository,
        WsActorManager $wsActorManager
    )
    {
        $this->userRepository = $userRepository;
        $this->typeManager = $typeManager;
        $this->fileManager = $fileManager;
        $this->em = $em;
        $this->themeManager = $themeManager;
        $this->convocationManager = $convocationManager;
        $this->sittingRepository = $sittingRepository;
        $this->wsActorManager = $wsActorManager;
    }



    /**
     * @param UploadedFile[] $uploadedFiles
     */
    public function createSitting(array $rawSitting, array $uploadedFiles, Structure $structure): Sitting
    {
        $this->validateRawSitting($rawSitting, $uploadedFiles, $structure);
        $sitting = new Sitting();
        $this->em->persist($sitting);

        $date = new DateTimeImmutable($rawSitting['date_seance']);
        $type = $this->typeManager->getOrCreateType($rawSitting['type_seance'], $structure);

        $this->associateActorsToType($type, $rawSitting['acteurs_convoques'] ?? null);

        $convocationFile = $this->fileManager->save($uploadedFiles['convocation'], $structure);

        $sitting->setStructure($structure)
            ->setType($type)
            ->setDate($date)
            ->setName($type->getName())
            ->setConvocationFile($convocationFile);

        $this->createProjectsAndAnnexes($rawSitting['projets'] ?? null, $uploadedFiles, $sitting);

        if (!$rawSitting['place']) {
            $sitting->setPlace($rawSitting['place']);
        }

        $this->convocationManager->createConvocationsActors($sitting);

        $this->em->flush();

        return $sitting;
    }

    private function associateActorsToType($type, ?string $rawActors): void
    {
        if (empty($rawActors)) {
            return;
        }

        $wsActors = $this->wsActorManager->validateAndFormatActor(json_decode($rawActors, true));
        if (!empty($wsActors)) {
            $this->wsActorManager->associateActorsToType($type, $wsActors);
        }
    }


    /**
     * @param UploadedFile[] $uploadedFiles
     */
    private function createProjectsAndAnnexes(array $rawProjects, array $uploadedFiles, Sitting $sitting): void
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
            throw new BadRequestHttpException('rapporteurlastname and rapporteurfirstname fields are required');
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

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    private function validateRawSitting(array $rawSitting, array $uploadedFiles, Structure $structure): void
    {
        if (!isset($rawSitting['date_seance'])) {
            throw new BadRequestHttpException('date_seance is required');
        }

        if (!isset($rawSitting['type_seance'])) {
            throw new BadRequestHttpException('type_seance is required');
        }

        if (!isset($uploadedFiles['convocation'])) {
            throw new BadRequestHttpException('convocation file is required');
        }

        if (!empty($rawSitting['acteurs_convoques'])) {
            try {
                json_decode($rawSitting['acteurs_convoques']);
            } catch (\Exception $e) {
                throw new BadRequestHttpException('acteurs_convoques must be json format');
            }
        }

        if ($this->isAlreadyExistsSitting($rawSitting, $structure)) {
            throw new BadRequestHttpException('sitting same type same datetime already exists');
        }
    }

    private function isAlreadyExistsSitting(array $rawSitting, Structure $structure): ?Sitting
    {
        return $this->sittingRepository->findOneBy([
            'name' => $rawSitting['type_seance'],
            'date' => new DateTimeImmutable($rawSitting['date_seance']),
            'structure' => $structure,
        ]);
    }


}
