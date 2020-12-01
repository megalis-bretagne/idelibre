<?php

namespace App\Controller\Connector;

use App\Form\Connector\ComelusConnectorType;
use App\Repository\Connector\ComelusConnectorRepository;
use App\Service\Connector\ComelusConnectorManager;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Configuration des connecteurs", routeName="connector_index")
 *
 */
class ComelusConnectorController extends AbstractController
{
    /**
     * @Route("/connector/comelus", name="comelus_connector")
     * @IsGranted("ROLE_MANAGE_CONNECTORS")
     * @Breadcrumb("Comelus")
     */
    public function edit(ComelusConnectorRepository $comelusConnectorRepository, ComelusConnectorManager $comelusConnectorManager,
                         Request $request): Response
    {
        $connector = $comelusConnectorRepository->getConnector($this->getUser()->getStructure());

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
}
