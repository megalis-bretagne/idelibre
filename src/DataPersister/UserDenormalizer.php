<?php

namespace App\DataPersister;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

class UserDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED_DENORMALIZER = 'UserDenormalizerCalled';

    public function __construct(private UserPasswordHasherInterface $passwordHasher, private Security $security)
    {
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        if (!empty($context[self::ALREADY_CALLED_DENORMALIZER])) {
            return false;
        }
        return ($type === User::class);
    }

    public function denormalize($data, string $type, string $format = null, array $context = []): User
    {
        $context[self::ALREADY_CALLED_DENORMALIZER] = true;
        /** @var User $user */
        $user = $this->denormalizer->denormalize($data, $type, $format, $context);
        if (!empty ($data["password"])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data["password"]));
        }
        if (!empty ($data["username"])) {
            $suffix = $this->security->getUser()->getStructure()->getSuffix();
            $user->setUsername($data["username"] . "@$suffix");
        }

        return $user;
    }
}
