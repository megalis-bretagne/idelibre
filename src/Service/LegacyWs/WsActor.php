<?php

namespace App\Service\LegacyWs;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class WsActor
{
    public string $firstName;
    public string $lastName;
    public ?string $title;
    public string $email;
    public ?string $phone;

    public function __construct(array $rawUser)
    {
        if (!$rawUser['Acteur']['prenom'] || !$rawUser['Acteur']['nom'] || !$rawUser['Acteur']['email']) {
            throw new BadRequestHttpException('fields ["acteurs_convoques"]["Acteur"][] ["prenom"] ["nom"] ["email"] are required');
        }

        $this->firstName = trim($rawUser['Acteur']['prenom']);
        $this->lastName = trim($rawUser['Acteur']['nom']);
        $this->title = isset($rawUser['Acteur']['titre']) ? trim($rawUser['Acteur']['titre']) : null;
        $this->email = trim($rawUser['Acteur']['email']);
        $this->phone = isset($rawUser['Acteur']['telmobile']) ? trim($rawUser['Acteur']['telmobile']) : null;
    }
}
