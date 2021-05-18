<?php

namespace App\Service\LegacyWs;

use App\Entity\Project;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\StructureRepository;
use App\Repository\UserRepository;
use App\Security\Password\LegacyPassword;
use App\Service\Convocation\ConvocationManager;
use App\Service\File\FileManager;
use App\Service\role\RoleManager;
use App\Service\Theme\ThemeManager;
use App\Service\Type\TypeManager;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LegacyWsService
{
    private StructureRepository $structureRepository;
    private UserRepository $userRepository;
    private UserPasswordEncoderInterface $passwordEncoder;
    private LegacyPassword $legacyPassword;
    private TypeManager $typeManager;
    private FileManager $fileManager;
    private EntityManagerInterface $em;
    private ThemeManager $themeManager;
    private RoleManager $roleManager;
    private ConvocationManager $convocationManager;

    public function __construct(
        EntityManagerInterface $em,
        StructureRepository $structureRepository,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        LegacyPassword $legacyPassword,
        TypeManager $typeManager,
        FileManager $fileManager,
        ThemeManager $themeManager,
        RoleManager $roleManager,
        ConvocationManager $convocationManager
    )
    {
        $this->structureRepository = $structureRepository;
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->legacyPassword = $legacyPassword;
        $this->typeManager = $typeManager;
        $this->fileManager = $fileManager;
        $this->em = $em;
        $this->themeManager = $themeManager;
        $this->roleManager = $roleManager;
        $this->convocationManager = $convocationManager;
    }

    public function getStructureFromLegacyConnection(string $legacyConnectionName): ?Structure
    {
        if (!$legacyConnectionName) {
            return null;
        }

        return $this->structureRepository->findOneBy(['legacyConnectionName' => $legacyConnectionName]);
    }

    public function loginUser(Structure $structure, string $username, string $plainPassword): ?User
    {
        $user = $this->userRepository->findOneSecretaryInStructure($structure, $username);

        if (!$user) {
            return null;
        }

        if (!$this->checkPassword($user, $plainPassword)) {
            return null;
        }

        return $user;
    }

    private function checkPassword(User $user, string $plainPassword): bool
    {
        if ($this->passwordEncoder->isPasswordValid($user, $plainPassword)) {
            return true;
        }

        return $this->legacyPassword->checkAndUpdateCredentials($user, $plainPassword);
    }

    /**
     * @param array $rawSitting
     * @param UploadedFile[] $uploadedFiles
     * @param Structure $structure
     */
    public function createSitting(array $rawSitting, array $uploadedFiles, Structure $structure)
    {
        //TODO transaction !

        $this->validateRawSitting($rawSitting, $uploadedFiles);
        $sitting = new Sitting();
        $this->em->persist($sitting);

        $date = new DateTimeImmutable($rawSitting['date_seance']);
        $type = $this->typeManager->getOrCreateType($rawSitting['type_seance'], $structure);

        $wsActors = $this->validateAndFormatActor(json_decode($rawSitting['acteurs_convoques'] ?? null, true) );
        if(!empty($wsActors)) {
            $this->associateActorsToType($type, $wsActors);
        }
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

    }


    /**
     * @param UploadedFile[] $uploadedFiles
     * @return Project[]
     */
    private function createProjectsAndAnnexes(array $rawProjects, array $uploadedFiles, Sitting $sitting): array
    {
        $projects = [];
        if (!$rawProjects) {
            return $projects;
        }

        foreach ($rawProjects as $rawProject) {
            $this->validateRawProject($rawProject, $uploadedFiles);
            $rank = $rawProject['ordre'];
            $project = new Project();
            $project->setRank($rank)
                ->setSitting($sitting)
                ->setName($rawProject['libelle'])
                //->setReporter()  //todo rapporteur
                ->setTheme($this->themeManager->createThemesFromString($rawProject['theme'], $sitting->getStructure()))
                ->setFile($this->fileManager->save($uploadedFiles['projet_' . $rank . '_rapport'], $sitting->getStructure()));
            //todo annex


            $this->em->persist($project);
            $projects[] = $project;
        }

        return $projects;
    }


    /**
     * @param WsActor[] $wsActors
     * @return User[]
     */
    private function associateActorsToType(Type $type, array $wsActors): void
    {
        $associatedActors = $this->userRepository->getAssociatedActorsWithType($type);
        if (empty($wsActors)) {
            return;
        }
        $uniqWsActors = $this->removeDuplicate($wsActors);
        $wsActorsToAdd = $this->getAddedActors($associatedActors, $uniqWsActors);
        $this->addWsActorsToType($wsActorsToAdd, $type);
    }


    /**
     * @param WsActor[] $wsActors
     */
    private function addWsActorsToType(array $wsActors, Type $type)
    {
        foreach ($wsActors as $wsActor) {
            $existingActor = $this->userRepository->findOneBy(
                ['firstName' => $wsActor->firstName, 'lastName' => $wsActor->lastName, 'structure' => $type->getStructure()]);
            if ($existingActor) {
                $type->addAssociatedUser($existingActor);
                continue;
            }

            $newActor = $this->createActorFromWsActor($wsActor, $type->getStructure());
            $type->addAssociatedUser($newActor);

        }
    }


    private function createActorFromWsActor(WsActor $wsActor, Structure $structure): User
    {
        $actor = (new User())
            ->setStructure($structure)
            ->setPassword("NotInitialiazed")
            ->setEmail($wsActor->email)
            ->setFirstName($wsActor->firstName)
            ->setLastName($wsActor->lastName)
            ->setUsername($this->generateUserName($wsActor->firstName, $wsActor->lastName, $structure->getSuffix()))
            ->setTitle($wsActor->title)
            ->setRole($this->roleManager->getActorRole())
            ->setIsActive(true);

        $this->em->persist($actor);

        return $actor;
    }


    private function generateUserName(string $firstName, string $lastName, string $suffix)
    {
        $normalizedFirstnameLetter = $this->slugify($firstName[0]);
        $normalizedLastName = $this->slugify($lastName);

        return "${normalizedFirstnameLetter}.${normalizedLastName}@${suffix}";
    }


    private function slugify($text)
    {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        if (function_exists('iconv')) {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }

        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }


    /**
     * @param User[] $associatedActors
     * @param WsActor[] $wsActors
     */
    private function getAddedActors(iterable $associatedActors, array $wsActors): array
    {
        $addedWsActors = [];
        foreach ($wsActors as $wsActor) {
            foreach ($associatedActors as $associatedActor) {
                if (strtolower($wsActor->firstName) === strtolower($associatedActor->getFirstName())
                    && strtolower($wsActor->lastName) === strtolower($associatedActor->getLastName())) {
                    continue 2;
                }
            }
            $addedWsActors[] = $wsActor;
        }

        return $addedWsActors;
    }


    /**
     * @param WsActor[] $wsActors
     * @return WsActor[]
     */
    private function removeDuplicate(array $wsActors): array
    {
        /** @var WsActor[] $uniqRawUsers */
        $uniqRawUsers = [];
        foreach ($wsActors as $wsActor) {
            foreach ($uniqRawUsers as $uniqUser) {
                if (strtolower($uniqUser->firstName) === strtolower($wsActor->firstName)
                    && strtolower($uniqUser->lastName) === strtolower($wsActor->lastName)) {
                    continue 2;
                }
            }
            $uniqRawUsers[] = $wsActor;
        }

        return $uniqRawUsers;
    }


    /**
     * @param $rawProject
     * @param UploadedFile[] $uploadedFiles
     */
    private function validateRawProject($rawProject, array $uploadedFiles): void
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
    private function validateRawSitting(array $rawSitting, array $uploadedFiles): void
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
    }


    /**
     * @param ?array $rawActors
     * @return ?WsActor[];
     */
    private function validateAndFormatActor(?array $rawActors): ?array
    {
        if (!$rawActors) {
            return null;
        }
        $wsActors = [];
        foreach ($rawActors as $rawActor) {
            $wsActors[] = new WsActor($rawActor);
        }
        return $wsActors;
    }


}
