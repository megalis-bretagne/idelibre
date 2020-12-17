<?php

namespace App\Service\EmailTemplate;

class DefaultTemplate
{
    public const FORGET_PASSWORD = 'Bonjour, <br>' . PHP_EOL . // NOSONAR
'Vous avez effectué une demande de remise à zéro de mot de passe <br>
Veuillez Cliquer ici pour le réinitialiser #reinitLink#';

    public const CONVOCATION = 'Bonjour, <br>
Vous êtes convoqué à la séance ... <br> ';

    public const INVITATION = 'Bonjour, <br>
Vous êtes invité à la séance ... <br>';
}
