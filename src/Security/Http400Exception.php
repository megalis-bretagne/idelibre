<?php

namespace App\Security;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Http400Exception extends HttpException
{
    public function __construct(?string $message = 'Bad request', Throwable $previous = null, array $headers = [], ?int $code = 400)
    {
        parent::__construct(400, $message, $previous, $headers, $code);
    }
}
