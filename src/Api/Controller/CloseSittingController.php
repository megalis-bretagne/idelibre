<?php

namespace App\Api\Controller;

use App\Entity\Sitting;
use App\Service\Seance\SittingManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CloseSittingController extends AbstractController
{
    public function __construct(private SittingManager $sittingManager)
    {
    }

    public function __invoke(Sitting $sitting): Sitting
    {
        $this->sittingManager->archive($sitting);

        return $sitting;
    }
}
