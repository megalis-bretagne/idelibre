<?php

namespace App\Service\User;

use App\Entity\Structure;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Email\EmailData;
use App\Service\Email\EmailNotSendException;
use App\Service\Email\EmailServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

class PasswordInvalidator
{
    public const INVALID_PASSWORD = 'CHANGEZMOI';
    public const INVALIDATE_PASSWORD_SUBJECT = 'Réinitialisation de votre mot de passe.';
    public const INVALIDATE_PASSWORD_CONTENT = 'Votre mot de passe doit être changé afin de correspondre aux exigences de sécurité. 
                    Veuillez utiliser l\'option "Mot de passe oublié" lors de votre prochaine connexion à nos services';

    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private EmailServiceInterface $emailService
    ) {
    }

    public function invalidatePassword(Structure $structure): void
    {
        $users = $this->userRepository->findByStructure($structure)->getQuery()->getResult();

        foreach ($users as $user) {
            $user->setPassword(self::INVALID_PASSWORD);
            $this->em->persist($user);
        }
        $this->em->flush();

        $this->prepareAndSendMail($users, $structure->getReplyTo());
    }

    /**
     * @param array<User> $users
     *
     * @throws EmailNotSendException
     */
    private function prepareAndSendMail(array $users, string $replyTo): void
    {
        $emailsData = [];

        foreach ($users as $user) {
            $subject = self::INVALIDATE_PASSWORD_SUBJECT;
            $content = self::INVALIDATE_PASSWORD_CONTENT;

            $emailData = new EmailData($subject, $content, EmailData::FORMAT_TEXT);
            $emailData->setTo($user->getEmail());
            $emailData->setReplyTo($replyTo);
            $emailsData[] = $emailData;
        }

        $this->emailService->sendBatch($emailsData);
    }
}
