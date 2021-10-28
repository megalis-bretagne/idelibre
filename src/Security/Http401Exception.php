<?php

namespace App\Security;

use Symfony\Component\HttpKernel\Exception\HttpException;

class Http401Exception extends HttpException
{
    public function __construct(?string $message = 'unAuthorized', \Throwable $previous = null, array $headers = [], ?int $code = 401)
    {
        parent::__construct(401, $message, $previous, $headers, $code);
    }
}
