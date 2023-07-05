<?php

namespace App\Controller\Connector;

use App\Form\Connector\LsvoteConnectorType;
use App\Repository\LsvoteConnectorRepository;
use App\Service\Connector\LsvoteConnectorManager;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Sidebar(active: ['configurations-nav'])]
#[Breadcrumb(title: 'Configuration des connecteurs', routeName: 'connector_index')]
class LsvoteConnectorController extends AbstractController
{
    public function __construct(
        private readonly LsvoteConnectorManager $lsvoteConnectorManager,
    ) {
    }

    #[Route('/lsvote/connector', name: 'lsvote_connector', methods: ['GET', 'POST'])]
    #[IsGranted(data: 'ROLE_MANAGE_CONNECTORS')]
    #[Breadcrumb(title: 'Lsvote')]
    public function edit(LsvoteConnectorRepository $lsvoteConnectorRepository, Request $request): Response
    {
        $connector = $lsvoteConnectorRepository->findOneBy(["structure" => $this->getUser()->getStructure()]);
        $form = $this->createForm(LsvoteConnectorType::class, $connector, ["structure" => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->lsvoteConnectorManager->save($form->getData());
            $this->addFlash('success', 'Le connecteur a bien été modifié');

            return $this->redirectToRoute('connector_index');
        }
        return $this->render('connector/lsvote.html.twig', [
            "form" => $form
        ]);
    }

    #[Route(path: '/connector/lsvote/check/', name: 'lsvote_connector_check')]
    #[IsGranted(data: 'ROLE_MANAGE_CONNECTORS')]
    public function isValidApiKey(Request $request): JsonResponse
    {
        $url = $request->query->get('url');
        $apiKey = $request->query->get('apiKey');

        $lsvoteInfo = $this->lsvoteConnectorManager->checkApiKey($url, $apiKey);
        if (!$lsvoteInfo) {
            return $this->json(null, 400);
        }

        return $this->json(["success" => true]);
    }
}
