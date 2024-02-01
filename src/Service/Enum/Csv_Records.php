<?php

namespace App\Service\Enum;

enum Csv_Records: int
{
    case GENDER = 0;
    case USERNAME = 1;
    case FIRST_NAME = 2;
    case LAST_NAME = 3;
    case EMAIL = 4;
    case ROLE = 5;
    case PHONE = 6;
    case TITLE = 7;
    case DEPUTY = 8;
    case TYPE_SEANCE = 9;
}
