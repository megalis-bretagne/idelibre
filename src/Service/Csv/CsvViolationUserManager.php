<?php

namespace App\Service\Csv;

use App\Entity\User;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class CsvViolationUserManager
{
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

    public function roleViolationForDeputy($record): ConstraintViolationList
    {
        $violation = new ConstraintViolation(
            'Seul un élu est en mesure d\'avoir un suppléant.',
            null,
            $record,
            null,
            'role',
            'Assignation du suppléant impossible'
        );

        return new ConstraintViolationList([$violation]);
    }

    public function violationUnautorizedPhone($record): ConstraintViolationList
    {
        $violation = new ConstraintViolation(
            'Seuls les élus et suppléants ont la possibilité d\'enregistrer leur numéro de téléphone.',
            null,
            $record,
            null,
            'phone',
            'Assignation du numéro de téléphone impossible'
        );

        return new ConstraintViolationList([$violation]);
    }

    public function violationUnautorizedTitle($record): ConstraintViolationList
    {
        $violation = new ConstraintViolation(
            'Seuls les élus ont la possibilité d\'enregistrer leur titre.',
            null,
            $record,
            null,
            'title',
            'Assignation du titre impossible'
        );

        return new ConstraintViolationList([$violation]);
    }
}
