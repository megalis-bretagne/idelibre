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
}
