<?php

namespace App\Controller\Connector;

use App\Form\Connector\LsmessageConnectorType;
use App\Repository\Connector\LsmessageConnectorRepository;
use App\Service\Connector\LsmessageConnectorManager;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Configuration des connecteurs", routeName="connector_index")
 * @Sidebar(active={"connector-nav"})
 */
class LsmessageConnectorController extends AbstractController
{
    /**
     * @Breadcrumb("Lsmessage")
     */
    #[Route(path: '/connector/lsmessage', name: 'lsmessage_connector')]
    #[IsGranted(data: 'ROLE_MANAGE_CONNECTORS')]
    public function edit(LsmessageConnectorRepository $lsmessageConnectorRepository, LsmessageConnectorManager $lsmessageConnectorManager, Request $request) : Response
    {
        $connector = $lsmessageConnectorRepository->findOneBy(['structure' => $this->getUser()->getStructure()]);
        $form = $this->createForm(LsmessageConnectorType::class, $connector);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $lsmessageConnectorManager->save($form->getData());
            $this->addFlash('success', 'Le connecteur a bien été modifié');

            return $this->redirectToRoute('connector_index');
        }
        return $this->render('connector/lsmessage.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/connector/lsmessage/check/', name: 'lsmessage_connector_check')]
    #[IsGranted(data: 'ROLE_MANAGE_CONNECTORS')]
    public function isValidApiKey(LsmessageConnectorManager $lsmessageConnectorManager, Request $request) : JsonResponse
    {
        $url = $request->query->get('url');
        $apiKey = $request->query->get('apiKey');
        $lsmessageInfo = $lsmessageConnectorManager->checkApiKey($url, $apiKey);
        if (null === $lsmessageInfo) {
            return $this->json(null, 400);
        }
        return $this->json(['balance' => $lsmessageInfo['balance']]);
    }
}
