<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait LoginTrait
{
    private function login(string $username)
    {
        $session = self::$container->get('session');

        $firewallName = 'main';
        $firewallContext = 'main';

        $userRepository = $this->entityManager->getRepository(User::class);
        /** @var User $user */
        $user = $userRepository->findOneBy(['username' => $username]);

        $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
        $session->set('_security_' . $firewallContext, serialize($token));

        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function loginAsAdminLibriciel()
    {
        $this->login('admin@libriciel');
    }

    public function loginAsSecretaryLibriciel()
    {
        $this->login('secretary1@libriciel.coop');
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
