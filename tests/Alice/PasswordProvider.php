<?php

namespace App\Tests\Alice;

use App\Entity\User;
use App\Security\Password\LegacyPassword;
use Faker\Generator;
use Faker\Provider\Base as BaseProvider;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordProvider extends BaseProvider
{
    private ?string $argon2iPassword;
    private ?string $sha1Password;

    private UserPasswordHasherInterface $userPasswordHasher;
    private LegacyPassword $legacyPassword;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, LegacyPassword $legacyPassword, Generator $generator)
    {
        parent::__construct($generator);
        $this->userPasswordHasher = $userPasswordHasher;
        $this->legacyPassword = $legacyPassword;
    }

    public function argon(string $plainPassword = 'password'): string
    {
        if ('password' === $plainPassword) {
            return $this->argon2iPassword ?? $this->argon2iPassword = $this->userPasswordHasher->hashPassword(new User(), 'password');
        }

        return $this->userPasswordHasher->hashPassword(new User(), $plainPassword);
    }

    public function legacyPassword(string $plainPassword = 'password'): string
    {
        if ('password' === $plainPassword) {
            return $this->sha1Password ?? $this->sha1Password = $this->legacyPassword->encode('password');
        }

        return  $this->legacyPassword->encode($plainPassword);
    }
}
