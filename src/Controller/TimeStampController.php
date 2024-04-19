<?php

namespace App\Controller;

use App\Entity\Sitting;
use App\Service\Timestamp\TimestampManager;
use App\Service\Zip\ZipTokenGenerator;
use Exception;
use Libriciel\LshorodatageApiWrapper\LsHorodatageException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TimeStampController extends AbstractController
{
    public function __construct(
        private readonly TimestampManager  $timestampManager,
        private readonly ZipTokenGenerator $zipTokenGenerator,
    )
    {
    }


    /**
     * @throws LsHorodatageException
     * @throws Exception
     */
    #[Route('/timestamp/sitting/{id}/verify', name: 'timestamp_list_sitting')]
    #[isGranted('ROLE_MANAGE_SITTINGS')]
    public function index(Sitting $sitting): Response
    {
        $timestamps = $this->timestampManager->listTimeStamps($this->zipTokenGenerator->getTimestampDirectory($sitting));

        return $this->render('timestamp/index.html.twig', [
            'title' => 'Vérification des jetons d\'horodatage de la séance ' . $sitting->getNameWithDate(),
            'tokens' => $this->timestampManager->extractTsaInfos($timestamps),
            'sitting' => $sitting,
        ]);
    }
}
