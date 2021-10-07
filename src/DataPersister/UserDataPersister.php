<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\Structure;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class UserDataPersister implements DataPersisterInterface
{
    public function __construct(private EntityManagerInterface $em, private Security $security)
    {
    }

    public function supports($data): bool
    {
        dump($data);

        return false;

        return $data instanceof User;
    }

    public function persist($data)
    {
        dd($data);
    }

    public function remove($data)
    {
        $this->em->remove($data);
        $this->em->flush();
    }

    public function getStructure(): Structure
    {
        /** @var User $user */
        $user = $this->security->getUser();

        return $user->getStructure();
    }
}
