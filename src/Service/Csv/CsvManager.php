<?php


namespace App\Service\Csv;

use App\Entity\Role;
use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Service\role\RoleManager;
use Doctrine\ORM\EntityManagerInterface;
use ForceUTF8\Encoding;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CsvManager
{
    public const TYPE_SEPARATOR = '|';

    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private ValidatorInterface $validator;
    private UserPasswordEncoderInterface $passwordEncoder;
    private RoleManager $roleManager;
    private TypeRepository $typeRepository;

    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserRepository $userRepository,
        TypeRepository $typeRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        RoleManager $roleManager
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
        $this->passwordEncoder = $passwordEncoder;
        $this->roleManager = $roleManager;
        $this->typeRepository = $typeRepository;
    }


    public function importUsers(UploadedFile $file, Structure $structure): array
    {
        $errors = [];
        $csvEmails = [];

        /** @var Reader $csv */
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $records = $csv->getRecords();

        foreach ($records as $record) {
            $username = $this->sanitize($record[0] ?? '');
            if (!$this->isExistUsername($username, $structure)) {
                $user = $this->createUserFromRecord($structure, $record);

                if ($this->validator->validate($user)->count() !== 0) {
                    $errors[] = $this->validator->validate($user);
                    continue;
                }

                if ($errorCsv = $this->isUsernameTwiceInCsv($csvEmails, $username, $user)) {
                    $errors[] = $errorCsv;
                    continue;
                }
                $csvEmails[] = $username;
                $this->associateActorToTypeSeances($user, $record[6] ?? null, $structure);
                $this->em->persist($user);
            }
        }
        $this->em->flush();

        return $errors;
    }


    private function associateActorToTypeSeances(User $user, ?string $typeNamesString, Structure $structure)
    {
        if (!$typeNamesString || $user->getRole()->getName() != 'Actor') {
            return;
        }
        $typeNames = explode(self::TYPE_SEPARATOR, $typeNamesString);

        foreach ($typeNames as $typeName) {
            $type = $this->typeRepository->findOneBy(['name' => $this->sanitize($typeName), 'structure' => $structure]);
            if (!$type) {
                $type = $this->createNewType($typeName, $structure);
            }
            $user->addAssociatedType($type);
        }
    }


    private function getRoleFromCode(int $roleId): ?Role
    {
        if ($roleId === 0) {
            return null;
        }
        $role = null;
        switch ($roleId) {
            case Role::SECRETARY:
                $role = $this->roleManager->getSecretaryRole();
                break;
            case Role::STRUCTURE_ADMIN:
                $role = $this->roleManager->getStructureAdminRole();
                break;
            case Role::ACTOR:
                $role = $this->roleManager->getActorRole();
                break;
            default:
                $role = null;
        }

        return $role;
    }

    private function isUsernameTwiceInCsv(array $csvEmails, string $email, User $user): ?ConstraintViolationListInterface
    {
        if (in_array($email, $csvEmails)) {
            $violation = new ConstraintViolation(
                'Le meme nom d\'utilisateur est déja présent dans ce csv. il n\'a donc pas été ajouté',
                null,
                ['username'],
                $user,
                'username',
                $user->getEmail()
            );
            return new ConstraintViolationList([$violation]);
        }
        return null;
    }


    private function isExistUsername(string $username, Structure $structure): bool
    {
        return $this->userRepository->count(['username' => $username, 'structure' => $structure]) !== 0;
    }

    private function sanitize(string $content): string
    {
        $trim_content = trim($content);
        // quick fix for non utf8 file
        return Encoding::toUTF8($trim_content);
    }


    private function createNewType(string $typeName, Structure $structure): Type
    {
        $type = new Type();
        $type->setName($this->sanitize($typeName))
            ->setStructure($structure);
        $this->em->persist($type);
        return $type;
    }


    private function createUserFromRecord(Structure $structure, array $record): User
    {
        $user = new User();
        $user->setUsername($this->sanitize($record[0] ?? ''))
            ->setFirstName($this->sanitize($record[1] ?? ''))
            ->setLastName($this->sanitize($record[2] ?? ''))
            ->setEmail($this->sanitize($record[3] ?? ''))
            ->setPassword($this->passwordEncoder->encodePassword($user, $this->sanitize($record[4] ?? '')))
            ->setRole($this->getRoleFromCode(intval($record[5] ?? 0)))
            ->setStructure($structure);
        return $user;
    }
}
