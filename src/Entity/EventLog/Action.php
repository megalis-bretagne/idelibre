<?php

namespace App\Entity\EventLog;

enum Action: string
{
    case SITTING_DELETE = 'Séance supprimée';
    case SITTING_ARCHIVED = 'Séance archivée';
    case USER_DELETE = 'Utilisateur supprimé';
    case USER_CREATE = 'utilisateur créé';
    case USER_PASSWORD_UPDATED = 'Mot de passe modifié';
}
