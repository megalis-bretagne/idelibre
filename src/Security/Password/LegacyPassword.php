<?php

namespace App\Security\Password;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LegacyPassword
{
    private string $salt;
    private UserPasswordEncoderInterface $passwordEncoder;
    private EntityManagerInterface $em;

    public function __construct(ParameterBagInterface $bag, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em)
    {
        $this->salt = $bag->get('legacy_salt');
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $em;
    }

    public function checkAndUpdateCredentials(UserInterface $user, string $plainPassword): bool
    {
        if (!$this->isPasswordValid($user->getPassword(), $plainPassword)) {
            return false;
        }

        $this->updatePassword($user, $plainPassword);

        return true;
    }

    private function isPasswordValid(string $userEncodedPassword, string $plainPassword): bool
    {
        $saltPlainPassword = $this->salt . $plainPassword;
        $encodedPassword = sha1($saltPlainPassword);

        return $encodedPassword === $userEncodedPassword;
    }

    private function updatePassword(UserInterface $user, string $plainPassword): void
    {
        $user->setPassword($this->passwordEncoder->encodePassword($user, $plainPassword));
        $this->em->persist($user);
        $this->em->flush();
    }

    public function encode(string $plainPassword): string
    {
        $saltPlainPassword = $this->salt . $plainPassword;

        return sha1($saltPlainPassword);
    }
}
