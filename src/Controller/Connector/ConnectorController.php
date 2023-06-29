<?php

namespace App\Controller\Connector;

use App\Repository\Connector\ComelusConnectorRepository;
use App\Repository\Connector\LsmessageConnectorRepository;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Sidebar(active: ['configurations-nav', 'connector-configuration-nav'])]
#[Breadcrumb(title: 'Configuration des connecteurs')]
class ConnectorController extends AbstractController
{
    #[Route(path: '/connector', name: 'connector_index')]
    #[IsGranted( 'ROLE_MANAGE_CONNECTORS')]
    public function index(ComelusConnectorRepository $comelusConnectorRepository, LsmessageConnectorRepository $lsmessageConnectorRepository): Response
    {
        return $this->render('connector/connector_index.html.twig', [
            'comelus' => $comelusConnectorRepository->findOneBy(['structure' => $this->getUser()->getStructure()]),
            'lsmessage' => $lsmessageConnectorRepository->findOneBy(['structure' => $this->getUser()->getStructure()]),
        ]);
    }
}
