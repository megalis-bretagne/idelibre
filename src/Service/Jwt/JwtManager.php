<?php

namespace App\Service\Jwt;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class JwtManager
{
    private string $key;

    public function __construct(ParameterBagInterface $bag)
    {
        $this->key = $bag->get('jwt_secret');
    }

    public function generate(array $payload): string
    {
        return JWT::encode($payload, $this->key, 'HS256');
    }

    /**
     * @throws JwtException
     */
    public function decode(string $jwt): array
    {
        try {
            $decodedToken = JWT::decode($jwt, new Key($this->key, 'HS256'));
        } catch (
            \InvalidArgumentException
            |\DomainException
            |\UnexpectedValueException
            |SignatureInvalidException
            |BeforeValidException
            |ExpiredException  $e) {
                throw new JwtException("token invalid");
            }

        return (array)$decodedToken;
    }

    public function generateTokenForUserNameAndSittingId(string $username, string $sittingId, bool $isAuthorizedMagicLink, \DateTimeInterface $dateTime): string
    {
        $tokenInfo = [
            'iss' => 'idelibre',
            'sub' => $username,
            'sittingId' => $sittingId,
            'isAuthorizedMagicLink' => $isAuthorizedMagicLink,
            'iat' => time(),
            'nbf' => time(),
            'exp' => $dateTime->getTimestamp()
        ];

        return $this->generate($tokenInfo);
    }
}
