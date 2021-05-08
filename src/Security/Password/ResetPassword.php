<?php

namespace App\Security\Password;

use App\Entity\ForgetToken;
use App\Entity\User;
use App\Repository\ForgetTokenRepository;
use App\Repository\UserRepository;
use App\Service\Email\EmailData;
use App\Service\Email\EmailServiceInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPassword
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ForgetTokenRepository
     */
    private $tokenRepository;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var EmailServiceInterface
     */
    private $email;

    public function __construct(
        EmailServiceInterface $email,
        EntityManagerInterface $em,
        RouterInterface $router,
        UserRepository $userRepository,
        ForgetTokenRepository $tokenRepository,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->tokenRepository = $tokenRepository;
        $this->router = $router;
        $this->passwordEncoder = $passwordEncoder;
        $this->email = $email;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function reset(string $username)
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);
        if (empty($user)) {
            throw new EntityNotFoundException('no user with username : ' . $username, 404);
        }

        $token = $this->createToken($user);

        $emailData = $this->prepareEmail($user, $token);
        $this->email->sendBatch([$emailData]);
    }

    public function prepareEmail(User $user, string $token): EmailData
    {
        $subject = 'Réinitialiser votre mot de passe';
        $resetPasswordUrl = $this->router->generate('app_reset', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
        $content = "Bonjour, \n Veuillez cliquer sur le lien suivant pour reinitialiser votre mot de passe \n " . $resetPasswordUrl;

        $emailData = new EmailData($subject, $content, EmailData::FORMAT_TEXT);
        $emailData->setTo($user->getEmail())
            ->setReplyTo($user->getStructure()->getReplyTo());

        return $emailData;
    }

    /**
     * @return User
     *
     * @throws EntityNotFoundException
     * @throws TimeoutException
     */
    public function getUserFromToken(string $token)
    {
        $token = $this->tokenRepository->findOneBy(['token' => $token]);

        if (empty($token)) {
            throw new EntityNotFoundException('this token does not exist', 400);
        }

        if (new DateTime() > $token->getExpireAt()) {
            throw new TimeoutException('this token has expired', 400);
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

    public function setNewPassword(User $user, string $plainPassword)
    {
        $user->setPassword($this->passwordEncoder->encodePassword($user, $plainPassword));
        $this->em->persist($user);
        $this->em->flush();

        $this->removeTokenIfExists($user);
    }
}
