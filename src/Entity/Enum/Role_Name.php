<?php

namespace App\Entity\Enum;

enum Role_Name: string
{
    public const NAME_ROLE_SECRETARY = 'Secretary';
    public const NAME_ROLE_STRUCTURE_ADMINISTRATOR = 'Admin';
    public const NAME_ROLE_ACTOR = 'Actor';
    public const NAME_ROLE_EMPLOYEE = 'Employee';
    public const NAME_ROLE_GUEST = 'Guest';
    public const NAME_ROLE_DEPUTY = "Deputy";
    public const INVITABLE_EMPLOYEE = [self::NAME_ROLE_EMPLOYEE, self:: NAME_ROLE_SECRETARY, self::NAME_ROLE_STRUCTURE_ADMINISTRATOR];

}
