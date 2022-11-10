<?php

namespace App\Tests;

use App\Repository\UserRepository;

trait LoginTrait
{
    public function login(string $username)
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneByUsername($username);
        $this->client->loginUser($user);
    }

    public function loginAsAdminLibriciel()
    {
        $this->login('admin@libriciel');
    }

    public function loginAsSecretaryLibriciel()
    {
        $this->login('secretary1@libriciel');
    }

    public function loginAsUserMontpellier()
    {
        $this->login('user@montpellier');
    }

    public function loginAsSuperAdmin()
    {
        $this->login('superadmin');
    }
}
