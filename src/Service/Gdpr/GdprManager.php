<?php

namespace App\Service\Gdpr;

use App\Entity\Gdpr;
use App\Repository\GdprRepository;
use Doctrine\ORM\EntityManagerInterface;

class GdprManager
{
    public function __construct(
        private GdprRepository $gdprRepository,
        private EntityManagerInterface $em
    )
    {
    }

    public function getGdpr(): ?Gdpr
    {
        return $this->gdprRepository->findOneBy([]);
    }

    public function save(Gdpr $gdpr): void
    {
        $this->em->persist($gdpr);
        $this->em->flush();
    }
}
