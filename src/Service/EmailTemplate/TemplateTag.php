<?php

namespace App\Service\EmailTemplate;

class TemplateTag
{
    public const SITTING_TYPE = '#typeseance#';
    public const SITTING_DATE = '#dateseance#';
    public const SITTING_TIME = '#heureseance#';
    public const SITTING_PLACE = '#lieuseance#';
    public const ACTOR_FIRST_NAME = '#prenom#';
    public const ACTOR_LAST_NAME = '#nom#';
    public const ACTOR_USERNAME = '#username#';
    public const ACTOR_TITLE = '#titre#';
    public const ACTOR_GENDER = '#civilite#';
    public const SITTING_URL = '#urlseance#';
    public const ACTOR_ATTENDANCE = '#presence#';
    public const ACTOR_DEPUTY = '#mandataire#';
    public const SITTING_RECAPITULATIF = '#recapitulatif#';
    public const FIRST_NAME_RECIPIENT = '#PRENOM_DESTINATAIRE#';
    public const LAST_NAME_RECIPIENT = '#NOM_DESTINATAIRE#';

    public const PRODUCT_NAME = '#NOM_PRODUIT#';
    public const INITIALIZATION_PASSWORD_LINK = '#LIEN_MDP_INITIALISATION#';
    public const FORGET_PASSWORD_LINK = '#LIEN_MDP_OUBLIE#';
    public const UPDATE_PASSWORD_LINK = '#LIEN_MDP_REACTUALISATION#';
}
