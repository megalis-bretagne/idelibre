<?php


namespace App\Service\Gdpr;

use App\Entity\Gdpr;
use App\Repository\GdprRepository;
use Doctrine\ORM\EntityManagerInterface;

class GdprManager
{
    private GdprRepository $gdprRepository;
    private EntityManagerInterface $em;

    public function __construct(GdprRepository $gdprRepository, EntityManagerInterface $em)
    {
        $this->gdprRepository = $gdprRepository;
        $this->em = $em;
    }

    public function getGdpr(): ?Gdpr
    {
        return $this->gdprRepository->findOneBy([]);
    }

    public function save(Gdpr $gdpr)
    {
        $this->em->persist($gdpr);
        $this->em->flush();
    }
}
