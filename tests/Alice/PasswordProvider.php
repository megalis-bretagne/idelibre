<?php

namespace App\Tests\Alice;

use App\Entity\User;
use Faker\Generator;
use Faker\Provider\Base as BaseProvider;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordProvider extends BaseProvider
{
    private UserPasswordHasherInterface $userPasswordHasher;
    private ?string $argon2iPassword;
    private ?string $sha1Password;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, Generator $generator)
    {
        parent::__construct($generator);
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function argon(string $plainPassword = 'password'): string
    {
        if ('password' === $plainPassword) {
            return $this->argon2iPassword ?? $this->argon2iPassword = $this->userPasswordHasher->hashPassword(new User(), 'password');
        }

        return $this->argon2iPassword = $this->userPasswordHasher->hashPassword(new User(), $plainPassword);
    }
}
