<?php

namespace App\Security\Password;

class PasswordChange
{
    public string $userId;
    public string $plainCurrentPassword;
    public string $plainNewPassword;
}
