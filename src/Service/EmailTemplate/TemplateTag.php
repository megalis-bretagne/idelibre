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
    public const FIRST_NAME_RECIPIENT = '#prenomdestinataire#';
    public const LAST_NAME_RECIPIENT = '#nomdestinataire#';

    public const PRODUCT_NAME = '#nomproduit#';
    public const INITIALIZATION_PASSWORD_URL = '#urlinitialisationmdp#';
    public const FORGET_PASSWORD_URL = '#urlmdpoublié#';
    public const UPDATE_PASSWORD_URL = '#urlreactualisationmdp#';
    public const CONFIRM_PRESENCE_URL = '#urlpresence#';
}
