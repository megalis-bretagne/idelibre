<?php

namespace App\Api\Controller;

use App\Entity\Sitting;
use App\Service\Convocation\ConvocationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SendSittingController extends AbstractController
{
    public function __construct(private ConvocationManager $convocationManager)
    {
    }

    public function __invoke(Sitting $sitting): Sitting
    {
        $this->convocationManager->sendAllConvocations($sitting, null);
        return $sitting;
    }

}
