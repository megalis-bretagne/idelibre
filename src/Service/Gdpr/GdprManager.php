<?php

namespace App\Service\Gdpr;

use App\Entity\GdprHosting;
use App\Repository\GdprHostingRepository;
use Doctrine\ORM\EntityManagerInterface;

class GdprManager
{
    public function __construct(
        private GdprHostingRepository  $gdprRepository,
        private EntityManagerInterface $em
    ) {
    }

    public function getGdpr(): ?GdprHosting
    {
        return $this->gdprRepository->findOneBy([]);
    }

    public function save(GdprHosting $gdpr): void
    {
        $this->em->persist($gdpr);
        $this->em->flush();
    }
}
