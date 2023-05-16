<?php

namespace App\Controller\Connector;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LsvoteConnectorController extends AbstractController
{
    #[Route('/lsvote/connector', name: 'app_lsvote_connector')]
    public function index(): Response
    {
        return $this->render('connector/lsvote.html.twig', [

        ]);
    }
}
