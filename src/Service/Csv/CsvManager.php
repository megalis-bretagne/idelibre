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

    /**
     * @return ConstraintViolationListInterface[]
     */
    public function importUsers(UploadedFile $file, Structure $structure): array
    {
        $errors = [];
        $csvEmails = [];

        /** @var Reader $csv */
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $records = $csv->getRecords();

        foreach ($records as $record) {
            if ($this->isMissingFields($record)) {
                $errors[] = $this->missingFieldViolation($record);
                continue;
            }

            $username = $this->sanitize($record[0] ?? '') . '@' . $structure->getSuffix();
            if (!$this->isExistUsername($username, $structure)) {
                $user = $this->createUserFromRecord($structure, $record);

                if (0 !== $this->validator->validate($user)->count()) {
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

    private function isMissingFields(array $record): bool
    {
        return 6 > count($record);
    }

    private function missingFieldViolation($record): ConstraintViolationList
    {
        $violation = new ConstraintViolation(
            'Chaque ligne doit contenir 6 champs separés par des virgules',
            null,
            $record,
            null,
            'le nombre de champs',
            'le nombre de champs est faux'
        );

        return new ConstraintViolationList([$violation]);
    }

    private function associateActorToTypeSeances(User $user, ?string $typeNamesString, Structure $structure): void
    {
        if (!$typeNamesString || 'Actor' != $user->getRole()->getName()) {
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
        if (0 === $roleId) {
            return null;
        }
        $role = null;
        switch ($roleId) {
            case Role::CODE_ROLE_SECRETARY:
                $role = $this->roleManager->getSecretaryRole();
                break;
            case Role::CODE_ROLE_STRUCTURE_ADMIN:
                $role = $this->roleManager->getStructureAdminRole();
                break;
            case Role::CODE_ROLE_ACTOR:
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
        return 0 !== $this->userRepository->count(['username' => $username, 'structure' => $structure]);
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
        $user->setUsername($this->sanitize($record[0] ?? '') . '@' . $structure->getSuffix())
            ->setFirstName($this->sanitize($record[1] ?? ''))
            ->setLastName($this->sanitize($record[2] ?? ''))
            ->setEmail($this->sanitize($record[3] ?? ''))
            ->setPassword($this->getPassword($user, $record[4] ?? ''))
            ->setRole($this->getRoleFromCode(intval($record[5] ?? 0)))
            ->setStructure($structure);

        return $user;
    }

    private function getPassword(User $user, string $plainPassword): string
    {
        $sanitizedPassword = $this->sanitize($plainPassword);
        if (0 === strlen($sanitizedPassword)) {
            return 'NotInitialized';
        }

        return  $this->passwordEncoder->encodePassword($user, $sanitizedPassword);
    }
}
