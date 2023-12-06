<?php

namespace App\Service\Csv;

use App\Entity\Structure;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Enum\Csv_Records;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CsvUserErrorManager
{

    public function __construct(
        private readonly ValidatorInterface      $validator,
        private readonly UserRepository          $userRepository,
    )
    {
    }

    public function isExistUsername(string $username, Structure $structure): bool
    {
        return 0 !== $this->userRepository->count(['username' => $username, 'structure' => $structure]);
    }

    public function preSavingValidation(array $record): ?ConstraintViolationList
    {
        if ($this->isMissingFields($record)) {
            return $this->missingFieldViolation($record);
        }

        if ($record[Csv_Records::USERNAME->value] === "") {
            return $this->missingUsernameViolation($record);
        }
        return null;
    }

    public function postSavingValidation(array $record, $user, $csvEmails): ?ConstraintViolationList
    {
        if (0 !== $this->validator->validate($user)->count()) {
            return $this->validator->validate($user);
        }

        if (!$user->getRole()) {
            return $this->missingRoleViolation($record);
        }

        if ($errorCsv = $this->isUsernameTwiceInCsv($csvEmails, $user->getUserName(), $user)) {
            return $errorCsv;
        }

        return null;
    }

    public function isUsernameTwiceInCsv(array $csvEmails, string $email, User $user): ?ConstraintViolationListInterface
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

    public function isMissingFields(array $record): bool
    {
        return count($record) < 6;
    }
    public function missingFieldViolation($record): ConstraintViolationList
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

    public function missingUsernameViolation($record): ConstraintViolationList
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

    public function missingRoleViolation($record): ConstraintViolationList
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
}
