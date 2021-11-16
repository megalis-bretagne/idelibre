<?php

namespace App\Controller\Connector;

use App\Form\Connector\ComelusConnectorType;
use App\Repository\Connector\ComelusConnectorRepository;
use App\Service\Connector\ComelusConnectorManager;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Libriciel\ComelusApiWrapper\ComelusException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Configuration des connecteurs", routeName="connector_index")
 */
#[Sidebar(active: ['configurations-nav'])]
class ComelusConnectorController extends AbstractController
{
    /**
     * @Breadcrumb("Comelus")
     */
    #[Route(path: '/connector/comelus', name: 'comelus_connector')]
    #[IsGranted(data: 'ROLE_MANAGE_CONNECTORS')]
    public function edit(ComelusConnectorRepository $comelusConnectorRepository, ComelusConnectorManager $comelusConnectorManager, Request $request): Response
    {
        $connector = $comelusConnectorRepository->findOneBy(['structure' => $this->getUser()->getStructure()]);
        $form = $this->createForm(ComelusConnectorType::class, $connector);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comelusConnectorManager->save($form->getData());
            $this->addFlash('success', 'Le connecteur a bien été modifié');

            return $this->redirectToRoute('connector_index');
        }

        return $this->render('connector/comelus.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/connector/comelus/check/', name: 'comelus_connector_check')]
    #[IsGranted(data: 'ROLE_MANAGE_CONNECTORS')]
    public function isValidApiKey(ComelusConnectorManager $comelusConnectorManager, Request $request): JsonResponse
    {
        $url = $request->query->get('url');
        $apiKey = $request->query->get('apiKey');
        if ($comelusConnectorManager->checkApiKey($url, $apiKey)) {
            return $this->json(null);
        }

        return $this->json(null, 400);
    }

    #[Route(path: '/connector/comelus/mailingLists', name: 'comelus_connector_mailing_lists')]
    #[IsGranted(data: 'ROLE_MANAGE_CONNECTORS')]
    public function getAvailableMailingLists(ComelusConnectorManager $comelusConnectorManager, Request $request): JsonResponse
    {
        $url = $request->query->get('url');
        $apiKey = $request->query->get('apiKey');
        try {
            return $this->json($comelusConnectorManager->getMailingLists($url, $apiKey));
        } catch (ComelusException $e) {
            return $this->json(null, 400);
        }
    }
}
