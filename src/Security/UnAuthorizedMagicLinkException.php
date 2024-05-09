<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UnAuthorizedMagicLinkException extends AuthenticationException
{

}