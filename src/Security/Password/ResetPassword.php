<?php

namespace App\Security\Password;

use App\Entity\ForgetToken;
use App\Entity\User;
use App\Repository\ForgetTokenRepository;
use App\Repository\UserRepository;
use App\Security\UserLoginEntropy;
use App\Service\Email\EmailServiceInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPassword
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ForgetTokenRepository $tokenRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EmailServiceInterface $email,
        private readonly UserRepository $userRepository,
        private readonly PasswordStrengthMeter $passwordStrengthMeter,
        private readonly UserLoginEntropy $userLoginEntropy
    ) {
    }

    /**
     * @throws EntityNotFoundException
     */
    public function sendEmailDefinePassword(User $user): void
    {
        $token = $this->createToken($user);
        $this->email->sendInitPassword($user, $token);
    }

    public function reloadPassword(User $user)
    {
        $token = $this->createToken($user);
        $this->email->sendReloadPassword($user, $token);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function reset(string $username): void
    {
        $user = $this->userRepository->findOneBy([
            'username' => $username
        ]);

        if (empty($user)) {
            throw new EntityNotFoundException('no user with username : ' . $username, Response::HTTP_NOT_FOUND);
        }

        $token = $this->createToken($user);

        $this->email->sendResetPassword($user, $token);
    }

    /**
     * @throws EntityNotFoundException
     * @throws TimeoutException
     */
    public function getUserFromToken(string $token): User
    {
        $token = $this->tokenRepository->findOneBy(['token' => $token]);
        if (empty($token)) {
            throw new EntityNotFoundException("this token does not exist", Response::HTTP_BAD_REQUEST);
        }

        if (new DateTime() > $token->getExpireAt()) {
            throw new TimeoutException('this token has expired', 498);
        }

        return $token->getUser();
    }

    /**
     * @throws EntityNotFoundException
     */
    private function createToken(User $user): string
    {
        $this->removeTokenIfExists($user);
        $token = new ForgetToken($user);
        $this->em->persist($token);
        $this->em->flush();

        return $token->getToken();
    }

    private function removeTokenIfExists(User $user)
    {
        $token = $this->tokenRepository->findOneBy(['user' => $user]);
        if (empty($token)) {
            return;
        }
        $this->em->remove($token);
        $this->em->flush();
    }

    public function setNewPassword(User $user, string $plainPassword): bool
    {
        $success = $this->checkPasswordEntropy($user, $plainPassword);

        if (false === $success) {
            return false;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $this->em->persist($user);
        $this->em->flush();

        $this->removeTokenIfExists($user);

        return true;
    }

    public function checkPasswordEntropy(User $user, string $plainPassword)
    {
        $passwordEntropy = $this->passwordStrengthMeter->getPasswordEntropy($plainPassword);

        if ($passwordEntropy <= $this->userLoginEntropy->getEntropy($user)) {
            return false;
        }

        return true;
    }
}
