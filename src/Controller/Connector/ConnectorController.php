<?php

namespace App\Controller\Connector;

use App\Annotation\Sidebar;
use App\Repository\Connector\ComelusConnectorRepository;
use App\Repository\Connector\LsmessageConnectorRepository;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Configuration des connecteurs")
 * @Sidebar(active={"connector-nav"})
 */
class ConnectorController extends AbstractController
{
    /**
     * @Route("/connector", name="connector_index")
     * @IsGranted("ROLE_MANAGE_CONNECTORS")
     */
    public function index(
        ComelusConnectorRepository $comelusConnectorRepository,
        LsmessageConnectorRepository $lsmessageConnectorRepository
    ): Response {
        return $this->render('connector/connector_index.html.twig', [
            'comelus' => $comelusConnectorRepository->findOneBy(['structure' => $this->getUser()->getStructure()]),
            'lsmessage' => $lsmessageConnectorRepository->findOneBy(['structure' => $this->getUser()->getStructure()]),
        ]);
    }
}
