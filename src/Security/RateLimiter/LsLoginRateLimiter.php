<?php

namespace App\Security\RateLimiter;

use Symfony\Component\HttpFoundation\RateLimiter\AbstractRequestRateLimiter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

final class LsLoginRateLimiter extends AbstractRequestRateLimiter
{
    public function __construct(private readonly RateLimiterFactory $usernameRateLimiterFactory)
    {
    }

    protected function getLimiters(Request $request): array
    {
        $username = $request->attributes->get(SecurityRequestAttributes::LAST_USERNAME, '');

        return [
            $this->usernameRateLimiterFactory->create($username)
        ];
    }
}
