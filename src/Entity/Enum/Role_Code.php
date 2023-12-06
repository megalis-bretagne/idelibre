<?php

namespace App\Entity\Enum;

enum Role_Code: string
{
    public const CODE_ROLE_SECRETARY = 1;
    public const CODE_ROLE_STRUCTURE_ADMIN = 2;
    public const CODE_ROLE_ACTOR = 3;
    public const CODE_ROLE_EMPLOYEE = 4;
    public const CODE_ROLE_GUEST = 5;
    public const CODE_ROLE_DEPUTY = 6;

}
