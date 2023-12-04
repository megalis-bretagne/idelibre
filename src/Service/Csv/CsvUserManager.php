<?php

namespace App\Service\Csv;

use App\Entity\Enum\Role_Code;
use App\Entity\Enum\Role_Name;
use App\Entity\Role;
use App\Entity\Structure;
use App\Entity\Timezone;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Service\Email\EmailNotSendException;
use App\Service\Enum\Csv_Records;
use App\Service\role\RoleManager;
use App\Service\Subscription\SubscriptionManager;
use App\Service\User\PasswordInvalidator;
use App\Service\Util\GenderConverter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use ForceUTF8\Encoding;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\stringContains;
use function PHPUnit\Framework\throwException;

class CsvUserManager
{
    public const TYPE_SEPARATOR = '|';
    public const INVALID_PASSWORD = 'CHANGEZMOI';

    public function __construct(
        private readonly EntityManagerInterface  $em,
        private readonly ValidatorInterface      $validator,
        private readonly UserRepository          $userRepository,
        private readonly TypeRepository          $typeRepository,
        private readonly RoleManager             $roleManager,
        private readonly SubscriptionManager     $subscriptionManager,
        private readonly CsvViolationUserManager $csvViolationUserManager,
    )
    {
    }

    /**
     * @return ConstraintViolationListInterface[]
     * @throws UnavailableStream
     * @throws \League\Csv\Exception
     */
    public function importUsers(UploadedFile $file, Structure $structure): array
    {
        $errors = [];
        $csvEmails = [];

        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $records = $csv->getRecords();

        $errors = $this->saveDeputyFirst($records, $structure, $csvEmails, $errors);

        foreach ($records as $record) {

            if ($this->csvViolationUserManager->isMissingFields($record)) {
                $errors[] = $this->csvViolationUserManager->missingFieldViolation($record);
                continue;
            }

            if ($record[1] === "") {
                $errors[] = $this->csvViolationUserManager->missingUsernameViolation($record);
                continue;
            }

            $username = $this->sanitize($record[Csv_Records::USERNAME->value] ?? '') . '@' . $structure->getSuffix();
            if (!$this->isExistUsername($username, $structure)) {

                $user = $this->createUserFromRecord($structure, $record);

                if ($this->isSecretaryOrAdmin($user)) {
                    $user->setSubscription($this->subscriptionManager->add($user));
                }

                if (0 !== $this->validator->validate($user)->count()) {
                    $errors[] = $this->validator->validate($user);
                    continue;
                }

                if (!$user->getRole()) {
                    $errors[] = $this->csvViolationUserManager->missingRoleViolation($record);
                    continue;
                }

                if ($errorCsv = $this->csvViolationUserManager->isUsernameTwiceInCsv($csvEmails, $username, $user)) {
                    $errors[] = $errorCsv;
                    continue;
                }


                $csvEmails[] = $username;
                $this->associateActorToTypeSeances($user, $record[Csv_Records::TYPE_SEANCE->value] ?? null, $structure);

                $this->em->persist($user);
                $this->em->flush();


                $this->assignDeputy($record, $user);

            }
        }

        return $errors;
    }




    private function isSecretaryOrAdmin(User $user): bool
    {
        if (empty($user->getRole())) {
            return false;
        }

        if (Role_Name::NAME_ROLE_SECRETARY === $user->getRole()->getName() || Role_Name::NAME_ROLE_STRUCTURE_ADMINISTRATOR === $user->getRole()->getName()) {
            return true;
        }

        return false;
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

        return match ($roleId) {
            Role_Code::CODE_ROLE_SECRETARY => $this->roleManager->getSecretaryRole(),
            Role_Code::CODE_ROLE_STRUCTURE_ADMIN => $this->roleManager->getStructureAdminRole(),
            Role_Code::CODE_ROLE_ACTOR => $this->roleManager->getActorRole(),
            Role_Code::CODE_ROLE_EMPLOYEE => $this->roleManager->getEmployeeRole(),
            Role_Code::CODE_ROLE_GUEST => $this->roleManager->getGuestRole(),
            Role_Code::CODE_ROLE_DEPUTY => $this->roleManager->getDeputyRole(),
            default => null,
        };
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

        $user
            ->setGender($this->getGenderCode(intval($record[Csv_Records::GENDER->value] ?? 0)))
            ->setUsername($this->sanitize($record[Csv_Records::USERNAME->value] ?? '') . '@' . $structure->getSuffix())
            ->setFirstName($this->sanitize($record[Csv_Records::FIRST_NAME->value] ?? ''))
            ->setLastName($this->sanitize($record[Csv_Records::LAST_NAME->value] ?? ''))
            ->setEmail($this->sanitize($record[Csv_Records::EMAIL->value] ?? ''))
            ->setRole($this->getRoleFromCode(intval($record[Csv_Records::ROLE->value] ?? 0)))
            ->setPhone($this->sanitizePhoneNumber($record[Csv_Records::PHONE->value] ?? ''))
            ->setPassword(self::INVALID_PASSWORD)
            ->setStructure($structure);


        return $user;
    }

    private function getGenderCode(?int $code): ?int
    {
        if (null === $code) {
            return null;
        }

        if (in_array($code, [GenderConverter::NOT_DEFINED, GenderConverter::FEMALE, GenderConverter::MALE])) {
            return $code;
        }

        return null;
    }

    private function sanitizePhoneNumber(string $phone): string
    {
        if (str_contains($phone, '.')) {
            return str_replace('.', '', $phone);
        }
        if (str_contains($phone, ' ')) {
            return str_replace(' ', '', $phone);
        }
        if (str_contains($phone, '-')) {
            return str_replace('-', '', $phone);
        }

        return $phone;
    }

    private function saveDeputyFirst(iterable $records, Structure $structure, $csvEmails, $errors): array
    {
        foreach ($records as $record) {
            if ($record[Csv_Records::ROLE->value] !== '6') {
                continue;
            }

            $validatationErrors = $this->validateFields($records);
            if ($validatationErrors) {
                $errors[] = $validatationErrors;
                continue;
            }

            $username = $this->sanitize($record[Csv_Records::USERNAME->value] ?? '') . '@' . $structure->getSuffix();
            if (!$this->isExistUsername($username, $structure)) {


                $user = $this->createUserFromRecord($structure, $record);

                if (0 !== $this->validator->validate($user)->count()) {
                    $errors[] = $this->validator->validate($user);
                    continue;
                }

                if (!$user->getRole()) {
                    $errors[] = $this->csvViolationUserManager->missingRoleViolation($record);
                    continue;
                }

                if ($errorCsv = $this->csvViolationUserManager->isUsernameTwiceInCsv($csvEmails, $username, $user)) {
                    $errors[] = $errorCsv;
                    continue;
                }


                $csvEmails[] = $username;
                $this->em->persist($user);
                $this->em->flush();
            }
        }
        return $errors;

    }


    private function validateFields($record): ?ConstraintViolationList
    {
        $errors = [];
        if ($this->csvViolationUserManager->isMissingFields($record)) {
            return $this->csvViolationUserManager->missingFieldViolation($record);

        }

        if ($record[1] === "") {
            return $this->csvViolationUserManager->missingUsernameViolation($record);

        }
        return null;
    }

    private function assignDeputy(array $record, $user): void
    {
        if ($this->isActorWithDeputy($record)) {

            $deputyUsername = $this->sanitize($record[Csv_Records::DEPUTY->value] . '@' . $user->getStructure()->getSuffix());

            $deputy = $this->userRepository->findOneBy(['username' => $deputyUsername, 'structure' => $user->getStructure()]);

            if ($deputy !== null) {
                $user->setDeputy($deputy);
                $this->em->persist($user);
                $this->em->flush();
            }
        }
    }

    private
    function isActorWithDeputy(array $record): bool
    {
        return $record[Csv_Records::ROLE->value] === '3' && !empty($record[Csv_Records::DEPUTY->value]);
    }




}
