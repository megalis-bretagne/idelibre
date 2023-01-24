<?php

namespace App\Security\Password;

use Exception;

class PasswordUpdaterException extends Exception
{
    public function __construct(string $message = '', public $minEntropyValue = null, public $currentEntropyValue = null)
    {
        parent::__construct($message, 0, null);
    }
}
