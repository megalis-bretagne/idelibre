<?php

namespace App\Entity\EventLog;

enum Action: string
{
    case DELETE_SITTING = "Séance supprimée";
    case ARCHIVE_SITTING = "Séance archivée";
    case SENT_CONVOCATIONS = "convocation envoyée";
    case MODIFY_SITTING = "Séance modfiée";
    case USER_DELETE = 'Utilisateur supprimé';
    case USER_CREATE = 'utilisateur créé';
    case USER_PASSWORD_UPDATED = 'Mot de passe modifié';
}
