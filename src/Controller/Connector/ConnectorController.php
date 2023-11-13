<?php

namespace App\Controller\Connector;

use App\Repository\Connector\ComelusConnectorRepository;
use App\Repository\Connector\LsmessageConnectorRepository;
use App\Repository\LsvoteConnectorRepository;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Sidebar(active: ['configurations-nav', 'connector-configuration-nav'])]
#[Breadcrumb(title: 'Configurations', routeName: 'configuration_index')]
#[Breadcrumb(title: 'Configuration des connecteurs', routeName: 'connector_index')]
class ConnectorController extends AbstractController
{
    #[Route(path: '/connector', name: 'connector_index')]
    #[IsGranted('ROLE_MANAGE_CONNECTORS')]
    public function index(ComelusConnectorRepository $comelusConnectorRepository, LsmessageConnectorRepository $lsmessageConnectorRepository, LsvoteConnectorRepository $lsvoteConnectorRepository): Response
    {
        return $this->render('connector/connector_index.html.twig', [
            'comelus' => $comelusConnectorRepository->findOneBy(['structure' => $this->getUser()->getStructure()]),
            'lsmessage' => $lsmessageConnectorRepository->findOneBy(['structure' => $this->getUser()->getStructure()]),
            'lsvote' => $lsvoteConnectorRepository->findOneBy(['structure' => $this->getUser()->getStructure()]),
        ]);
    }
}
