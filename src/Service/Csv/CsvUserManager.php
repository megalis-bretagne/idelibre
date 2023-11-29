<?php

namespace App\Service\Csv;

use App\Entity\Role;
use App\Entity\Structure;
use App\Entity\Timezone;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Service\Email\EmailNotSendException;
use App\Service\role\RoleManager;
use App\Service\Subscription\SubscriptionManager;
use App\Service\User\PasswordInvalidator;
use App\Service\Util\GenderConverter;
use Doctrine\ORM\EntityManagerInterface;
use ForceUTF8\Encoding;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function PHPUnit\Framework\stringContains;

class CsvUserManager
{
    public const TYPE_SEPARATOR = '|';
    public const INVALID_PASSWORD = 'CHANGEZMOI';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly UserRepository $userRepository,
        private readonly TypeRepository $typeRepository,
        private readonly RoleManager $roleManager,
        private readonly SubscriptionManager $subscriptionManager,
    ) {
    }

    /**
     * @return ConstraintViolationListInterface[]
     */
    public function importUsers(UploadedFile $file, Structure $structure): array
    {
        $errors = [];
        $csvEmails = [];

        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $records = $csv->getRecords();

        foreach ($records as $record) {

//            dd($this->sanitizePhoneNumber($record[6]) );

            if ($this->isMissingFields($record)) {
                $errors[] = $this->missingFieldViolation($record);
                continue;
            }

            if ($record[1] === "") {
                $errors[] = $this->missingUsernameViolation($record);
                continue;
            }

            $username = $this->sanitize($record[1] ?? '') . '@' . $structure->getSuffix();
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
                    $errors[] = $this->missingRoleViolation($record);
                    continue;
                }

                if ($errorCsv = $this->isUsernameTwiceInCsv($csvEmails, $username, $user)) {
                    $errors[] = $errorCsv;
                    continue;
                }

                $csvEmails[] = $username;
                $this->associateActorToTypeSeances($user, $record[5] ?? null, $structure);

//                if (preg_match('/[0-9]/', $record[6]) ) {
//                    throw new \Exception('Le numéro de téléphone n\'est pas au bon format');
//                }

                $this->em->persist($user);
                $this->em->flush();
            }
        }

        return $errors;
    }



    private function isMissingFields(array $record): bool
    {
        return  7 > count($record);
    }

    private function isSecretaryOrAdmin(User $user): bool
    {
        if (empty($user->getRole())) {
            return false;
        }

        if (Role::NAME_ROLE_SECRETARY === $user->getRole()->getName() || Role::NAME_ROLE_STRUCTURE_ADMINISTRATOR === $user->getRole()->getName()) {
            return true;
        }

        return false;
    }

    private function missingFieldViolation($record): ConstraintViolationList
    {
        $violation = new ConstraintViolation(
            'Chaque ligne doit contenir 6 champs séparés par des virgules.',
            null,
            $record,
            null,
            'le nombre de champs',
            'le nombre de champs est faux'
        );

        return new ConstraintViolationList([$violation]);
    }

    private function missingUsernameViolation($record): ConstraintViolationList
    {
        $violation = new ConstraintViolation(
            'La colonne username doit être renseignée pour chaque entrée',
            null,
            $record,
            null,
            'username',
            'Username est vide'
        );

        return new ConstraintViolationList([$violation]);
    }

    private function missingRoleViolation($record): ConstraintViolationList
    {
        $violation = new ConstraintViolation(
            'Il est obligatoire de définir un role parmi les valeurs 1, 2, 3, 4, 5 ou 6.',
            null,
            $record,
            null,
            'role',
            'le role n\'est pas bon'
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

        return match ($roleId) {
            Role::CODE_ROLE_SECRETARY => $this->roleManager->getSecretaryRole(),
            Role::CODE_ROLE_STRUCTURE_ADMIN => $this->roleManager->getStructureAdminRole(),
            Role::CODE_ROLE_ACTOR => $this->roleManager->getActorRole(),
            Role::CODE_ROLE_EMPLOYEE => $this->roleManager->getEmployeeRole(),
            Role::CODE_ROLE_GUEST => $this->roleManager->getGuestRole(),
            Role::CODE_ROLE_DEPUTY => $this->roleManager->getDeputyRole(),
            default => null,
        };
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
        $user
            ->setGender($this->getGenderCode(intval($record[0] ?? 0)))
            ->setUsername($this->sanitize($record[1] ?? '') . '@' . $structure->getSuffix())
            ->setFirstName($this->sanitize($record[2] ?? ''))
            ->setLastName($this->sanitize($record[3] ?? ''))
            ->setEmail($this->sanitize($record[4] ?? ''))
            ->setRole($this->getRoleFromCode(intval($record[5] ?? 0)))

            ->setPhone($this->sanitizePhoneNumber($record[6]) ?? '' )

            ->setTitle($record[5] === '3' ? $this->sanitize( $record[7]) : null )
            ->setPassword("CHANGEZ-MOI")
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

    /**
     * @throws \Exception
     */
    private function sanitizePhoneNumber(string $phone): string
    {
        if (str_contains($phone, '+')) {
            $phone = "0" . substr($phone, 3);
            return str_contains($phone, ' ') ? str_replace(' ', '', $phone): $phone;
        }

        if (str_contains($phone, '.')) {
            return str_replace('.', '', $phone) ;
        }
        if (str_contains($phone, ' ')) {
            return str_replace(' ', '', $phone);
        }
        if (str_contains($phone, '-')) {
            return str_replace('-', '', $phone);
        }

        return $phone;
    }
}
