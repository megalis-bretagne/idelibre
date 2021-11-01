<?php

namespace App\Controller\Connector;

use App\Repository\Connector\ComelusConnectorRepository;
use App\Repository\Connector\LsmessageConnectorRepository;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Configuration des connecteurs")
 */
#[Sidebar(active: ['connector-nav', 'connector-configuration-nav'])]
class ConnectorController extends AbstractController
{
    #[Route(path: '/connector', name: 'connector_index')]
    #[IsGranted(data: 'ROLE_MANAGE_CONNECTORS')]
    public function index(ComelusConnectorRepository $comelusConnectorRepository, LsmessageConnectorRepository $lsmessageConnectorRepository): Response
    {
        return $this->render('connector/connector_index.html.twig', [
            'comelus' => $comelusConnectorRepository->findOneBy(['structure' => $this->getUser()->getStructure()]),
            'lsmessage' => $lsmessageConnectorRepository->findOneBy(['structure' => $this->getUser()->getStructure()]),
        ]);
    }
}
