<?php


namespace App\Service\Gdpr;


use App\Entity\Gdpr;
use App\Repository\GdprRepository;

class GdprManager
{

    private GdprRepository $gdprRepository;

    public function __construct(GdprRepository $gdprRepository)
    {
        $this->gdprRepository = $gdprRepository;
    }

    public function getGdpr(): ?Gdpr
    {
        return $this->gdprRepository->findOneBy([]);
    }

}
