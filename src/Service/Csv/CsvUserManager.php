<?php

namespace App\Service\Csv;

use App\Entity\Enum\Role_Code;
use App\Entity\Enum\Role_Name;
use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Service\Enum\Csv_Records;
use App\Service\Subscription\SubscriptionManager;
use App\Service\Util\GenderConverter;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class CsvUserManager
{
    public const TYPE_SEPARATOR = '|';
    public const INVALID_PASSWORD = 'CHANGEZMOI';

    public function __construct(
        private readonly EntityManagerInterface  $em,
        private readonly UserRepository          $userRepository,
        private readonly TypeRepository          $typeRepository,
        private readonly SubscriptionManager     $subscriptionManager,
        private readonly CsvUserErrorManager     $csvUserErrorManager,
        private readonly DataFormatter           $dataFormatter,
    )
    {
    }

    /**
     * @return ConstraintViolationListInterface[]
     * @throws UnavailableStream
     * @throws Exception
     */
    public function importUsers(UploadedFile $file, Structure $structure): array
    {
        $errors = [];
        $csvEmails = [];

        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $records = $csv->getRecords();

        $errors = $this->saveDeputy($records, $structure, $csvEmails, $errors);

        return [...$errors, ...$this->saveOthers($records, $structure, $csvEmails, $errors)];
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
            $type = $this->typeRepository->findOneBy(['name' => $this->dataFormatter->sanitize($typeName), 'structure' => $structure]);
            if (!$type) {
                $type = $this->createNewType($typeName, $structure);
            }
            $user->addAssociatedType($type);
        }
    }

    private function createNewType(string $typeName, Structure $structure): Type
    {
        $type = new Type();
        $type->setName($this->dataFormatter->sanitize($typeName))
            ->setStructure($structure);
        $this->em->persist($type);

        return $type;
    }

    private function createUserFromRecord(Structure $structure, array $record): User
    {
        $user = new User();

        $user
            ->setGender($this->dataFormatter->getGenderCode(intval($record[Csv_Records::GENDER->value] ?? 0)))
            ->setUsername($this->dataFormatter->formatUsername($record[Csv_Records::USERNAME->value], $structure))
            ->setFirstName($this->dataFormatter->sanitize($record[Csv_Records::FIRST_NAME->value] ?? ''))
            ->setLastName($this->dataFormatter->sanitize($record[Csv_Records::LAST_NAME->value] ?? ''))
            ->setEmail($this->dataFormatter->sanitize($record[Csv_Records::EMAIL->value] ?? ''))
            ->setRole($this->dataFormatter->getRoleFromCode(intval($record[Csv_Records::ROLE->value] ?? 0)))
            ->setPhone($this->dataFormatter->sanitizePhoneNumber($record[Csv_Records::PHONE->value] ?? ''))
            ->setTitle($this->dataFormatter->actorTitle($record))
            ->setPassword(self::INVALID_PASSWORD)
            ->setStructure($structure);


        return $user;
    }

    private function saveDeputy(iterable $records, Structure $structure, $csvEmails, $errors): array
    {
        foreach ($records as $record) {

            if ($record[Csv_Records::ROLE->value] !== strval(Role_Code::CODE_ROLE_DEPUTY)) {
                continue;
            }

            $validationErrors = $this->csvUserErrorManager->preSavingValidation($record);
            if ($validationErrors) {
                $errors[] = $validationErrors;
                continue;
            }

            $username = $this->dataFormatter->formatUsername($record[Csv_Records::USERNAME->value] ?? '', $structure);
            if (!$this->csvUserErrorManager->isExistUsername($username, $structure)) {

                $user = $this->createUserFromRecord($structure, $record);

                $postValidationErrors = $this->csvUserErrorManager->postSavingValidation($record, $user, $csvEmails);
                if ($postValidationErrors) {
                    $errors[] = $postValidationErrors;
                    continue;
                }

                $csvEmails[] = $username;
                $this->em->persist($user);
                $this->em->flush();
            }
        }
        return $errors;

    }

    private function saveOthers($records, $structure, $csvEmails, $errors): array
    {
        foreach ($records as $record) {

            $validatationErrors = $this->csvUserErrorManager->preSavingValidation($record);
            if ($validatationErrors) {
                $errors[] = $validatationErrors;
                continue;
            }

            $username = $this->dataFormatter->formatUsername($record[Csv_Records::USERNAME->value] ?? '', $structure);
            if (!$this->csvUserErrorManager->isExistUsername($username, $structure)) {

                $user = $this->createUserFromRecord($structure, $record);

                if ($this->isSecretaryOrAdmin($user)) {
                    $user->setSubscription($this->subscriptionManager->add($user));
                }

                $postValidationErrors = $this->csvUserErrorManager->postSavingValidation($record, $user, $csvEmails);
                if ($postValidationErrors) {
                    $errors[] = $postValidationErrors;
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

    private function assignDeputy(array $record, $user): void
    {
        if ($this->isActorWithDeputy($record)) {

            $deputyUsername = $this->dataFormatter->formatUsername($record[Csv_Records::DEPUTY->value] ?? '', $user->getStructure());
            $deputy = $this->userRepository->findOneBy(['username' => $deputyUsername, 'structure' => $user->getStructure()]);

            if ($deputy !== null) {
                $user->setDeputy($deputy);
                $this->em->persist($user);
                $this->em->flush();
            }
        }
    }

    private function isActorWithDeputy(array $record): bool
    {
        return $record[Csv_Records::ROLE->value] === strval(Role_Code::CODE_ROLE_ACTOR) && !empty($record[Csv_Records::DEPUTY->value]);
    }

}
