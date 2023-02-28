<?php

namespace App\Entity\EventLog;

enum Action: string
{
    case DELETE_SITTING = "Séance supprimée";
    case ARCHIVE_SITTING = "Séance archivée";
    case SENT_CONVOCATIONS = "convocation envoyée";
    case MODIFY_SITTING = "Séance modfiée";
    case DELETE_USER = 'Utilisateur supprimé';
    case CREATE_USER = 'utilisateur créé';
    case CHANGE_PASSWORD = 'Mot de passe modifié';
}
