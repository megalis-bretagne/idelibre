<?php


namespace App\Security\Password;

use App\Entity\ForgetToken;
use App\Entity\User;
use App\Repository\ForgetTokenRepository;
use App\Repository\UserRepository;
use App\Service\Email\IEmail;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
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
     * @var IEmail
     */
    private $email;

    public function __construct(
        IEmail $email,
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
     * @param string $username
     * @throws EntityNotFoundException
     *
     */
    public function reset(string $username)
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);
        if (empty($user)) {
            throw new EntityNotFoundException("no user with username : " . $username, 404);
        }

        $token =$this->createToken($user);
        $this->email->sendReinitPassword($user, $token);
    }

    /**
     * @param string $token
     * @return User
     * @throws EntityNotFoundException
     * @throws TimeoutException
     */
    public function getUserFromToken(string $token)
    {
        $token = $this->tokenRepository->findOneBy(['token' => $token]);
        if (empty($token)) {
            throw new EntityNotFoundException("this token does not exist", 400);
        }
        if (new DateTime() > $token->getExpireAt()) {
            throw new TimeoutException("this token has expired", 400);
        }
        return $token->getUser();
    }


    /**
     * @param User $user
     * @return string
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
