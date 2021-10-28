<?php

namespace App\Security;

use Symfony\Component\HttpKernel\Exception\HttpException;

class Http403Exception extends HttpException
{
    public function __construct(?string $message = 'forbidden', \Throwable $previous = null, array $headers = [], ?int $code = 403)
    {
        parent::__construct(403, $message, $previous, $headers, $code);
    }
}
